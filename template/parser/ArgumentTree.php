<?php

namespace sylma\template\parser;
use sylma\core, sylma\template as tpl, sylma\parser\reflector;

class ArgumentTree extends core\module\Domed implements tpl\parser\tree {

  protected $handler;
  protected $sName;

  public function __construct(tpl\parser\handler $handler, core\argument $arg) {

    $this->setHandler($handler);
    $this->setArguments($arg);
    $this->setNamespace($arg->getNamespace());
    $this->setName($arg->getRoot());
  }

  protected function setName($sName) {

    $this->sName = $sName;
  }

  protected function getName() {

    return $this->sName;
  }

  protected function setHandler(tpl\parser\handler $handler) {

    $this->handler = $handler;
  }

  protected function getHandler() {

    return $this->handler;
  }

  public function reflectRead(array $aArguments = array()) {

    return $this->getHandler()->trimString($this->getArguments()->read());
  }

  public function reflectApply($sMode, array $aArguments = array()) {

    if ($result = $this->getHandler()->lookupTemplate($this->getName(), $this->getNamespace(), $sMode)) {

      $result->setTree($this);
      $result->sendArguments($aArguments);
    }
    else {

      if (!$sMode) {

        $this->launchException('Cannot apply tree without mode');
      }
    }

    return $result;
  }

  public function reflectApplyFunction($sName, array $aPath, $sMode) {

    $aResult = array();

    switch ($sName) {

      case 'name' :

        $aResult[] = $this->getArguments()->getRoot();
        break;

      default :

        $this->launchException("Function '$sName' unknown in argument tree");
    }

    return $aResult;
  }

  public function reflectApplyAll($sMode, array $aArguments = array()) {

    $aResult = array();

    foreach ($this->getArguments() as $child) {

      $aResult[] = $this->loadChild($child)->reflectApply($sMode, $aArguments);
    }

    return $aResult;
  }

  protected function loadChild(core\argument $content) {

    return new self($this->getHandler(), $content);
  }

  public function reflectApplyDefault($sPath, array $aPath, $sMode, $bRead) {

    $args = $this->getArguments();

    if ($aPath) {

      $this->launchException('Not ready, should avoid path parsing');
    }

    if ($bRead) {

      $result = $args->read($sPath);
    }
    else {

      $tree = $this->loadChild($args->get($sPath));
      $result = $this->getHandler()->applyArrayTo($tree, $aPath, $sMode);
    }

    return $result;
  }

  public function asToken() {

    return "[Tree]" . $this->getNamespace() . ':' . $this->getName();
  }
}

