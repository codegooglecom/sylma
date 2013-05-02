<?php

namespace sylma\view\parser\builder;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\storage\fs, sylma\view\parser\crud;

class Router extends View {

  const ROUTE_DEFAULT = 'default';

  public function build() {

    $this->setDirectory(__FILE__);

    $doc = $this->getDocument();
    $root = $doc->getRoot();

    if ($root->getName() == 'view') {

      $this->launchException('"view" element instead of "crud"', get_defined_vars());
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

      //$this->setWindow($this->createWindow());

      $result->parseRoot($this->getDocument()->getRoot());
    }
    catch (core\exception $e) {

      $this->catchException($this->getFile(), $e);
    }

    return $result;
  }

  protected function buildCrud () {

    $reflector = $this->buildCrudReflector();
    //$window = $this->getWindow();

    $aRoutes = $reflector->getRoutes();

    $window = $this->prepareWindow(self::MODE_DEFAULT);
    //$this->setWindow($window);

    //$window->createVariable('arguments', '\sylma\core\argument');
    $result = $window->addVar($window->createVar($window->argToInstance(null), 'result'));

    $switch = $this->createSwitch($window);

    if ($route = $reflector->getDefault()) {

      $route->setAlias(self::ROUTE_DEFAULT);
      $arguments = $window->getVariable(self::ARGUMENTS_NAME);

      $if = $window->createCondition(
              $window->createNot($arguments->call('query')),
              $arguments->call('add', array(self::ROUTE_DEFAULT)));

      $window->add($if);
    }

    foreach ($aRoutes as $sub) {

      if ($sub instanceof crud\Route) {

        $content = $this->reflectRoute($sub, $window);
      }
      else {

        $content = $this->reflectViewComponent($sub, $window);
      }

      $switch->addCase($sub->getAlias(), $content);
    }

    $window->add($switch);
    $window->setReturn($result);

    return $this->createFile($this->loadTarget($this->getDocument(), $this->getFile()), $this->buildWindow($window));
  }

  protected function reflectViewComponent(crud\View $view, common\_window $window) {

    $file = $this->buildView($view->asDocument(), $this->loadSelfTarget($this->getFile(), $view->getName()));

    return $this->callScript($file, $window, $window->tokenToInstance('\sylma\dom\handler'));
  }

  protected function callScript(fs\file $file, common\_window $window, $return = null) {

    $arguments = $window->getVariable('aSylmaArguments');

    //$closure = $window->createClosure(array($arguments));
    //$closure->addContent($window->callFunction('include', $return, array($file->getName())));

    $call = $window->createCall($window->getSylma(), 'includeFile', $return, array($file->getRealPath(), $arguments));

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
    $view = $this->buildView($main->asDocument(), $this->loadSelfTarget($file, $main->getName()));

    $sub = $route->getSub();
    $form = $this->buildView($sub->asDocument(), $this->loadSelfTarget($file, $sub->getName()));

    $arguments = $window->getVariable(self::ARGUMENTS_NAME);

    $getArgument = $arguments->call(self::ARGUMENT_METHOD);
    $result = $window->createCondition($window->createTest($getArgument, $sub->getAlias(true), '=='));

    $result->addContent($arguments->call('shift'));
    $result->addContent($this->callScript($form, $window, $window->tokenToInstance('php-integer')));
    $result->addElse($this->callScript($view, $window, $window->tokenToInstance('\sylma\dom\handler')));

    return $result;
  }

  protected function createSwitch(common\_window $window) {

    $call = $window->createCall($window->getVariable(self::ARGUMENTS_NAME), 'shift', 'php-string');
    $result = $window->createSwitch($call);

    return $result;
  }
}

