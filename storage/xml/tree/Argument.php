<?php

namespace sylma\storage\xml\tree;
use sylma\core, sylma\dom;

class Argument extends _Callable {

  protected $handler;
  protected $options;

  protected $sName;
  protected $bRoot = false;

  public function parseRoot(dom\element $el) {

    parent::parseRoot($el);

    if ($sFile = $this->readx('@file')) {

      $this->setFile($this->getSourceFile($sFile));

      $options = $this->createOptions((string) $this->getFile());
      $this->setOptions($options);
    }
  }

  public function setOptions(core\argument $arg) {

    $this->setNamespace($arg->getNamespace());
    $this->setName($arg->getRoot());

    $this->options = $arg;
  }

  protected function getOptions() {

    return $this->options;
  }

  public function isRoot($bValue = null) {

    if (is_bool($bValue)) $this->bRoot = $bValue;

    return $this->bRoot;
  }

  protected function setName($sName) {

    $this->sName = $sName;
  }

  protected function getName() {

    return $this->sName;
  }

  public function reflectRead(array $aArguments = array()) {

    return $this->getParser()->trimString($this->getOptions()->read());
  }

  public function reflectApply($sMode = '', array $aArguments = array()) {

    if ($result = $this->getParser()->lookupTemplate($this->getName(), $this->getNamespace(), $sMode, $this->isRoot())) {

      $result->setTree($this);
      $result->sendArguments($aArguments);
    }
    else {

      if (!$sMode) {

        $this->launchException('No template found, cannot apply tree');
      }
    }

    return $result;
  }

  public function reflectApplyFunction($sName, array $aPath, $sMode, $bRead = false, $sArguments = '', array $aArguments = array()) {

    $aResult = array();

    switch ($sName) {

      case 'name' :

        $aResult[] = $this->getOptions()->getRoot();
        break;

      case 'read' : $aResult[] = $this->reflectRead($aArguments); break;

      default :

        $aResult = parent::reflectApplyFunction($sName, $aPath, $sMode, $bRead, $sArguments, $aArguments);
    }

    return $aResult;
  }

  public function reflectApplyAll($sMode, array $aArguments = array()) {

    $aResult = array();

    foreach ($this->getOptions() as $child) {

      $aResult[] = $this->loadChild($child)->reflectApply($sMode, $aArguments);
    }

    return $aResult;
  }

  protected function loadChild(core\argument $content) {

    $result = new static($this->getParser());
    $result->setOptions($content);

    return $result;
  }

  public function reflectApplyDefault($sPath, array $aPath = array(), $sMode = '', $bRead = false) {

    $args = $this->getOptions();

    if ($aPath) {

      $this->launchException('Not ready, todo : avoid path parsing');
    }

    if ($bRead) {

      $result = $args->read($sPath, false);
    }
    else {

      $el = $args->get($sPath, false, false);
      $result = null;

      if ($el) {

        //$this->launchException("Unknown value : '$sPath'");

        if ($el->getType() == $el::ELEMENT) { // && $el->isComplex()

          $tree = $this->loadChild($args->get($sPath));
          $result = $this->getParser()->applyArrayTo($tree, $aPath, $sMode);
        }
        else {

          $result = $this->reflectApplyDefault($sPath, $aPath, $sMode, true);
        }
      }
    }

    return $result;
  }

  public function asToken() {

    return $this->getNamespace() . ':' . $this->getName();
  }
}

