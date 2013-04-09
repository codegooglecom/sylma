<?php

namespace sylma\view\parser;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\storage\fs;

class Router extends Builder {

  const MODE_DEFAULT = 'view';

  public function build() {

    parent::build();

    $doc = $this->getDocument();
    $root = $doc->getRoot();

    if ($root->getName() == 'view') {

      $this->setMode(self::MODE_DEFAULT);
      $result = $this->buildDefault();
    }
    else if ($root->getName() == 'crud') {

      $result = $this->buildCrud();
    }

    return $result;
  }

  protected function buildCrudReflector() {

    try {

      $class = $this->getFactory()->findClass('crud');
      $result = $this->create('crud', array($this, null, $class));

      $this->setWindow($this->createWindow());

      $result->parseRoot($this->getDocument()->getRoot());
    }
    catch (core\exception $e) {

      $this->catchException($this->getFile(), $e);
    }

    return $result;
  }

  protected function buildCrud () {

    $reflector = $this->buildCrudReflector();
    $aRoutes = $reflector->getRoutes();

    $window = $this->prepareArgumented();
    //$this->setWindow($window);

    $window->createVariable('arguments', '\sylma\core\argument');
    $result = $window->addVar($window->createVar($window->argToInstance(null), 'result'));

    $switch = $this->createSwitch($window);

    foreach ($aRoutes as $sub) {

      if ($sub instanceof crud\Route) $content = $this->reflectRoute($sub, $window);
      else $content = $this->reflectViewComponent($sub, $window);

      $switch->addCase($sub->getAlias(), $content);
    }

    $window->add($switch);
    $window->setReturn($result);

    return $this->createFile($this->loadTarget($this->getDocument(), $this->getFile()), $this->buildWindow($window));
  }

  protected function addToResult() {


  }

  protected function reflectViewComponent(crud\View $view, common\_window $window) {

    $content = $this->reflectView($view->asDocument(), $this->prepareArgumented(), false, $view->getMode());
    $file = $this->createFile($this->loadSelfTarget($this->getFile(), $view->getName()), $this->buildInstanciation($content));

    return $this->createCall($file, $window, $window->tokenToInstance('\sylma\dom\handler'));
  }

  protected function createCall(fs\file $file, common\_window $window, $return = null) {

    $arguments = $window->getVariable('arguments');

    //$closure = $window->createClosure(array($arguments));
    //$closure->addContent($window->callFunction('include', $return, array($file->getName())));

    $call = $window->createCall($window->getSylma(), 'includeFile', $return, array($file->getRealPath(), array('arguments' => $arguments)));

    if ($return) {

      $result = $window->createAssign($window->getVariable('result'), $call);
    }
    else {

      $result = $call;
    }

    return $result;
  }

  protected function reflectRoute(crud\Route $route, common\_window $window) {

    $file = $this->getFile();

    $main = $route->getMain();
    $winView = $this->prepareArgumented();
    $content = $this->reflectView($main->asDocument(), $winView, false, $main->getMode());
    $view = $this->createFile($this->loadSelfTarget($file, $main->getName()), $this->buildInstanciation($content, $winView));

    $sub = $route->getSub();
    $winForm = $this->prepareArgumented();
    $content = $this->reflectView($sub->asDocument(), $winForm, true, $sub->getMode());
    $form = $this->createFile($this->loadSelfTarget($file, $sub->getName()), $this->buildSimple($content, $winForm));

    $arguments = $window->getVariable('arguments');

    $getArgument = $window->createCall($arguments, 'read', 'php-string', array('do', false));
    $result = $window->createCondition($getArgument);

    $result->addContent($this->createCall($form, $window));
    $result->addElse($this->createCall($view, $window, $window->tokenToInstance('\sylma\dom\handler')));

    return $result;
  }

  protected function createSwitch(common\_window $window) {

    $call = $window->createCall($window->getVariable('arguments'), 'shift', 'php-string');
    $result = $window->createSwitch($call);

    return $result;
  }

  public function buildRoot($sMode) {

    switch ($sMode) {

      case '' :
      case 'view' :

        $view = $this->buildView();
        $this->buildRegistered($view);
        break;

      case 'update' :

        $view = $this->buildView();
        $this->buildRegistered($view, 'update');
        break;

      case 'insert' :

        $view = $this->buildInsert();
        $this->buildRegistered($view, 'create');
        break;

      case 'delete' :

        $this->buildDelete();
        break;
    }
  }

  protected function loadSelfTarget(fs\file $file, $sMode = '') {

    if ($sMode) {

      $result = $this->getManager()->getCachedFile($file, ".{$sMode}.php");
    }
    else {

      $result = parent::loadSelfTarget($file);
    }

    return $result;
  }

  protected function prepareArgumented() {

    $result = $this->createWindow();
    $result->setVariable($result->createVariable('arguments', '\sylma\core\argument'));

    return $result;
  }

  protected function _createRouter(fs\file $viewFile) {

    $window = $this->createWindow();

    $return = $window->tokenToInstance('\sylma\dom\handler');

    $arguments = $window->createVariable('arguments', '\sylma\core\argument');
    $result = $window->addVar($window->argToInstance(''));
    $isset = $window->callFunction('isset', $window->tokenToInstance('php-boolean'), array($arguments));
    //$init = $window->createCall($window->getSelf(), 'getManager', '\sylma\core\Initializer', array('init'));
    $getArguments = $window->createInstanciate($window->tokenToInstance(get_class($this->create('argument'))));

    $window->add($window->createCondition($window->createNot($isset), $window->createAssign($arguments, $getArguments)));


    $getArgument = $window->createCall($arguments, 'read', 'php-string', array(0));

    $callView = $window->createClosure(array($arguments));
    $callView->addContent($window->callFunction('include', $return, array($viewFile->getName())));
    $assign = $window->createAssign($result, $window->callClosure($callView, $return, array($arguments)));
    $window->add($assign);
    $window->createCondition($getArgument, $assign);

    $window->setReturn($result);

    return $window;
  }

  public function _build($sMode = '') {

    return $this->buildRoot($sMode);

    $file = $this->getFile();
    $doc = $this->getDocument();

    $this->setMode('view');

    $view = $this->reflectMain($file, $doc, $this->prepareArgumented());
    $reflector = $this->getReflector();

    if ($reflector->getRegistered()) {

      $view = $this->createFile($this->loadSelfTarget($file, 'view'), $this->buildInstanciation($view));

      $this->setDirectory(__FILE__);

      $this->setMode('insert');
      $this->setArguments(self::FORM_ARGUMENTS);

      $form = $this->createFile($this->loadSelfTarget($file, 'insert'), $this->buildSimple($file, $doc, $this->prepareArgumented()));
      $router = $this->createRouter($view, $form);

      $result = $this->createFile($this->loadSelfTarget($file), $this->buildWindow($router));
    }
    else {

      $result = $this->createFile($this->loadSelfTarget($file), $this->buildInstanciation($view));
    }

    return $result;
  }

}

