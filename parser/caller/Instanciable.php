<?php

namespace sylma\parser\caller;
use sylma\core, sylma\parser, sylma\storage\fs, sylma\dom, sylma\parser\action\php;

require_once('Domed.php');
require_once('parser/caller.php');

class Instanciable extends Domed implements parser\caller {

  public function getInstance(php\_window $window, dom\collection $children) {

    $aArguments = $this->parseArguments($children);

    $file = $this->getClassFile();

    $require = $window->createFunction('require_once', $window->argToInstance('php-bool'), array($file->getRealPath()));
    $window->add($require);

    $instance = $window->loadInstance($this->getName(), (string) $file);
    
    return $window->createInstanciate($instance, $aArguments);
  }

  protected function getClassFile() {

    if (!$sFile = $this->readArgument('file')) {

      $sFile = str_replace('\\', '/', $this->getName()) . '.php';
    }

    return $this->getControler()->getFile($sFile);
  }
}