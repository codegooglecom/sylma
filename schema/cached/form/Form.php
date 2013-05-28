<?php

namespace sylma\schema\cached\form;
use sylma\core;

class Form extends core\module\Argumented {

  protected $sMode;
  protected $contexts;
  protected $aElements = array();

  public function __construct(core\argument $arguments, core\argument $post, core\argument $contexts, $sMode) {

    $this->setMode($sMode);

    $this->setArguments($arguments);
    $this->setContexts($contexts);
    $this->setSettings($post);
  }

  protected function checkToken($sToken) {


  }

  protected function setMode($sMode) {

    $this->sMode = $sMode;
  }

  protected function getMode() {

    return $this->sMode;
  }

  protected function setContexts(core\argument $contexts) {

    $this->contexts = $contexts;
  }

  protected function getContext($sName) {

    return $this->contexts->get($sName);
  }

  public function addMessage($sMessage, array $aArguments = array()) {

    $this->getContext('messages')->add(array(
      'content' => $sMessage,
      'arguments' => $aArguments,
    ));
  }

  public function addElement($sName, Simple $element) {

    $element->setHandler($this);
    $this->aElements[$sName] = $element;
  }

  protected function getElement($sName, $bDebug = true) {

    if (!isset($this->aElements[$sName])) {

      if ($bDebug) $this->launchException("Element $sName does not exists");
    }

    return $this->aElements[$sName];
  }

  protected function getElements() {

    return $this->aElements;
  }

  public function readElement($sName, $bEscape = true, $bDebug = true) {

    if ($el = $this->getElement($sName, $bDebug)) {

      $sResult = $bEscape ? $el->escape() : $el->getValue();
    }
    else {

      $sResult = $bEscape ? "''" : '';
    }

    return $sResult;
  }

  public function validate() {

    $bValid = true;

    foreach ($this->getElements() as $sName => $element) {

      if (!$element->validate()) $bValid = false;
    }

    return $bValid;
  }
}

