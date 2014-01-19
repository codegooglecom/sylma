<?php

namespace sylma\template\binder\component;
use sylma\core, sylma\dom, sylma\parser\languages\common;

class Method extends Basic implements common\arrayable {

  protected $sName;
  protected $sValue;

  public function parseRoot(dom\element $el) {

    $this->setNode($el);

    $window = $this->getWindow();

    $this->loadValue($el);

    if ($sArguments = $this->readx('@arguments')) {

      $aArguments = explode(',', $sArguments);
    }
    else {

      $aArguments = array();
    }

    $function = $window->createFunction($aArguments, $this->getValue());
    $this->loadName();

    $this->getParser()->getObject()->setMethod($this->getName(), $function);
  }

  protected function loadName() {

    $this->setName($this->readx('@name'));
  }

  protected function loadValue(dom\element $el) {

    $this->sValue = $this->parseContent($el->read());
  }

  protected function getValue() {

    return $this->sValue;
  }

  protected function setName($sName) {

    $this->sName = $sName;
  }

  public function getName() {

    return $this->sName;
  }

  protected function parseContent($sContent) {

    $aReplaces = array(
      '/%([\w-_]+)%/' => '\$(this).retrieve(\'sylma-$1\')',
      '/%([\w-_]+)\s*,\s*([^%]+)%/' => '\$(this).store(\'sylma-$1\', $2)');

    $sResult = preg_replace(array_keys($aReplaces), $aReplaces, $sContent);

    return $sResult;
  }

  public function asArray() {

    return array();
  }
}

