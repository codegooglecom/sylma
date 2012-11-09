<?php

namespace sylma\parser\caller;
use sylma\core, sylma\parser, sylma\storage\fs, sylma\dom, sylma\parser\languages\common, sylma\parser\languages\php;

class Instanciable extends Domed implements parser\caller {

  public function getInstance(common\_window $window, dom\collection $children) {

    $aArguments = $this->parseArguments($children);

    $file = $this->getClassFile();

    $require = $window->callFunction('require_once', $window->argToInstance('php-bool'), array($file->getRealPath()));
    $window->add($require);

    $instance = $window->loadInstance($this->getName(), (string) $file);

    return $window->createInstanciate($instance, $aArguments);
  }

  protected function getClassFile() {

    if (!$sFile = $this->readArgument('file')) {

      $sFile = str_replace('\\', '/', $this->getName()) . '.php';
    }

    return $this->getControler('fs')->getFile($sFile, $this->getFile()->getParent());
  }
}