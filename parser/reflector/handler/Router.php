<?php

namespace sylma\view\parser;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\storage\fs;

class Router extends reflector\handler\Documented {

  protected $reflector;

  protected function getReflector() {

    return $this->reflector;
  }

  protected function setReflector(Elemented $reflector) {

    $this->reflector = $reflector;
  }

  protected function prepareArgumented() {

    $result = $this->createWindow();
    $result->setVariable($result->createVariable('arguments', '\sylma\core\argument'));

    return $result;
  }

  protected function createRouter(fs\file $viewFile) {

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

  protected function loadSelfTarget(fs\file $file, $sMode = '') {

    if ($sMode) {

      $result = $this->getManager()->getCachedFile($file, ".{$sMode}.php");
    }
    else {

      $result = parent::loadSelfTarget($file);
    }

    return $result;
  }

  protected function createReflector() {

    $result = parent::createReflector();
    //$result->setMode($this->getMode());

    $this->setReflector($result);

    return $result;
  }

  protected function parseReflector(reflector\domed $reflector, dom\document $doc) {

    return $reflector->parseRoot($doc->getRoot(), $this->getMode());
  }
}

