<?php

namespace sylma\schema\cached;
use sylma\core;

class Form extends core\module\Argumented {

  protected $sMode;
  protected $contexts;
  protected $aElements = array();

  public function __construct(core\argument $arg, core\argument $contexts, $sMode) {

    $this->setMode($sMode);
    $this->setContexts($contexts);
    $this->setSettings($arg);
  }

  protected function checkToken($sToken) {


  }

  protected function setMode($sMode) {

    $this->sMode = $sMode;
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

  protected function getElements() {

    return $this->aElements;
  }

  public function readElement($sName) {

    return $this->aElements[$sName]->escape();
  }

  public function validate() {

    $bValid = true;

    foreach ($this->getElements() as $sName => $element) {

      if ($bValid) $bValid = $element->validate();
    }

    return $bValid;
  }
}

