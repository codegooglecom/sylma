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

  protected function getContext($sName, $bDebug = true) {

    return $this->contexts->get($sName, $bDebug);
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

  protected function removeElement($sName) {

    unset($this->aElements[$sName]);
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

  public function validate() {

    $bValid = true;

    foreach ($this->getElements() as $sName => $element) {

      if (!$element->validate()) $bValid = false;
    }

    if (!$bValid) {

      $this->addMessage('Some fields are missing or invalids, they have been highlighted');
    }
    else if ($this->getContext('messages', false)) {

      $this->addMessage('Datas has been ' . ($this->getMode() == 'insert' ? 'inserted' : 'updated'));
    }

    return $bValid;
  }

  protected function buildInsert() {

    $aKeys = $aValues = array();

    foreach ($this->getElements() as $sName => $el) {

      $aValues[] = $el->escape();
      $aKeys[] = $sName;
    }

    $sKeys = implode(',', $aKeys);
    $sValues = implode(',', $aValues);

    return ' (' . $sKeys . ') VALUES (' . $sValues . ')';
  }

  protected function buildUpdate() {

    $aResult = array();

    foreach ($this->getElements() as $sName => $el) {

      $aResult[] = '`' . $sName . '`' . '=' . $el->escape();
    }

    return implode(',', $aResult);
  }

  public function asString() {

    if (!$this->getElements()) {

      $this->launchException('Cannot update table without registered field');
    }

    return $this->getMode() === 'insert' ? $this->buildInsert() : $this->buildUpdate();
  }
}

