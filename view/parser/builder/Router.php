<?php

namespace sylma\view\parser\builder;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\storage\fs, sylma\view\parser\crud;

class Router extends View {

  const CRUD_NS = 'http://2013.sylma.org/view/crud';

  protected $currentView;

  public function build() {

    $this->setDirectory(__FILE__);

    $doc = $this->getDocument();
    $root = $doc->getRoot();

    if ($root->getName() != 'crud') {

      $this->launchException('Crud root element expected', get_defined_vars());
    }

    return $this->buildCrud();
  }

  protected function buildCrudReflector() {

    try {

      $class = $this->getFactory()->findClass('crud');
      $result = $this->create('crud', array($this, null, $class));

      //$this->setWindow($this->createWindow());

      $result->parseRoot($this->getDocument()->getRoot());
    }
    catch (core\exception $e) {

      $this->catchException($e, $this->getFile());
    }

    return $result;
  }

  protected function buildCrud () {

    $reflector = $this->buildCrudReflector();
    //$window = $this->getWindow();

    if (!$aPaths = $reflector->getPaths()) {

      $this->launchException('No path defined');
    }

    $this->setPaths($aPaths);

    $window = $this->prepareWindow($this->getDocument(), self::MODE_DEFAULT);
    //$this->setWindow($window);

    //$window->createVariable('arguments', '\sylma\core\argument');
    $result = $window->addVar($window->createVar($window->argToInstance(null), 'result'));

    $switch = $this->createSwitch($window);

    if ($path = $reflector->getDefault()) {

      $arguments = $window->getVariable(self::ARGUMENTS_NAME);

      $if = $window->createCondition(
              $window->createNot($arguments->call('query')),
              $arguments->call('add', array($path->getAlias())));

      $window->add($if);
    }

    foreach ($aPaths as $path) {

      if (!$path->isDisabled()) {

        if ($path instanceof crud\Route) {

          $content = $this->reflectRoute($path, $window);
        }
        else {

          $content = $this->reflectViewComponent($path, $window);
        }

        $switch->addCase($path->getAlias(), $content);
      }
    }

    $window->add($switch);
    $window->setReturn($result);

    return $this->createFile($this->loadTarget($this->getDocument(), $this->getFile()), $this->buildWindow($window));
  }

  protected function setPaths(array $aPaths) {

    $this->aPaths = $aPaths;
  }

  protected function setView(crud\View $view) {

    $this->currentView = $view;
  }

  public function getPath($sPath) {

    $aPath = explode('/', $sPath);
    $sName = current($aPath);

    if (isset($this->aPaths[$sName])) {

      array_shift($aPath);
      $view = $this->aPaths[$sName];
    }
    else {

      $view = $this->getDefault();
    }

    return $view ? $view->getPath($aPath) : null;
  }

  protected function getDefault() {

    return isset($this->aPaths['']) ? $this->aPaths[''] : null;
  }

  public function asPath() {

    return $this->getView()->asPath();
  }

  protected function getView() {

    if (!$this->currentView) {

      $this->launchException('No view defined');
    }

    return $this->currentView;
  }

  protected function buildReflector(common\_window $window = null) {

    $result = parent::buildReflector($window);
    $result->setNamespace(self::CRUD_NS, 'crud');

    return $result;
  }

  protected function buildCrudView(crud\View $view) {

    $doc = $view->asDocument();
    $file = $this->getPathFile($view);
    $this->setView($view);

    try {

      $result = parent::buildView($doc, $file);
    }
    catch (core\exception $e) {

      $e->addPath('@view ' . $view->getAlias());
      $this->loadLog();
      throw $e;
    }

    return $result;
  }

  public function getPathFile(crud\Pathed $path) {

    $file = $this->getFile();
    $sMode = $path->getAlias();

    if ($sMode) {

      $result = $this->getManager()->getCachedFile($file, ".{$sMode}.php");
    }
    else {

      $result = $this->loadSelfTarget($file);
    }

    return $result;
  }

  protected function reflectViewComponent(crud\View $view, common\_window $window) {

    $file = $this->buildCrudView($view);
    $this->setView($view);

    return $this->callScript($file, $window, $window->tokenToInstance('\sylma\dom\handler'));
  }

  protected function reflectRoute(crud\Route $route, common\_window $window) {

    $main = $route->getMain();
    $view = $this->buildCrudView($main);

    $sub = $route->getSub();
    $form = $this->buildCrudView($sub);

    $arguments = $window->getVariable(self::ARGUMENTS_NAME);

    $getArgument = $arguments->call(self::ARGUMENT_METHOD);
    $result = $window->createCondition($window->createTest($getArgument, $sub->getName(), '=='));

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

