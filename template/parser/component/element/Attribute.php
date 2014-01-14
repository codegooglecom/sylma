<?php

namespace sylma\template\parser\component\element;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\template;

class Attribute extends template\parser\component\Child implements common\arrayable, template\parser\component {

  protected $sName;
  protected $var;

  public function init($sName, $mValue = null) {

    if ($mValue) {

      $mValue = is_array($mValue) ? $this->getWindow()->flattenArray($mValue) : array($mValue);
      $aArguments = array($sName, $mValue);
    }
    else {

      $aArguments = array($sName);
    }

    $this->setVar($this->createObject('cached', $aArguments));
    $this->setName($sName);
  }

  protected function setVar(common\_var $var) {

    $this->var = $var;
  }

  public function getVar() {

    return $this->var;
  }

  protected function setName($sName) {

    $this->sName = $sName;
  }

  public function getName() {

    return $this->sName;
  }

  public function asValue() {

    $el = $this->getNode();

    $content = $this->parseChildren($el->getChildren());

    if (is_array($content) && count($content) === 1) {

      $content = current($content);
    }

    return $content;
  }

  public function addToken($val) {

    return $this->getWindow()->createInstruction($this->getVar()->call('addToken', array($val)));
  }

  public function asArray() {

    return array($this->getVar());
  }
}

