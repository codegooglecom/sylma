<?php

namespace sylma\schema\parser\component;
use sylma\core, sylma\dom, sylma\schema\parser, sylma\parser\reflector;

class Basic extends reflector\component\Foreigner {

  protected $sName = '';

  public function parseRoot(dom\element $el) {

    $this->setNode($el, false);
  }

  public function setName($sName) {

    $this->sName = $sName;
  }

  public function getName() {

    return $this->sName;
  }

  public function asToken() {

    return $this->getName();
  }

  public function getNamespace($sPrefix = '') {

    return parent::getNamespace($sPrefix);
  }

  protected function parseReflector(array $aArguments = array(), $bStatic = false) {

    if ($this->getNode(false) and $sClass = $this->readx('@' . 'reflector' . ($bStatic ? '-static' : ''))) {

      if ($bStatic) {

        $result = $this->createClass($sClass);
      }
      else {

        $result = $this->createObject($sClass, $aArguments, null, false);
      }
    }
    else {

      $result = false;
    }

    return $result;
  }
}

