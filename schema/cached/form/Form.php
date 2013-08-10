<?php

namespace sylma\schema\cached\form;
use sylma\core;

class Form extends core\module\Argumented {

  protected $sMode;
  protected $contexts;
  protected $aElements = array();
  protected $bSub = false;

  public function __construct(core\argument $arguments, core\argument $post, core\argument $contexts, $sMode, Token $token = null, $bSub = false) {

    if ($token) {

      $token->isValid();
    }

    $this->setMode($sMode);

    $this->setArguments($arguments);
    $this->setContexts($contexts);
    $this->setSettings($post);
    $this->isSub($bSub);
  }

  protected function checkToken($sPath) {


  }

  protected function isSub($bVal = null) {

    if (is_bool($bVal)) $this->bSub = $bVal;

    return $this->bSub;
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

    if (!$this->contexts) {

      if ($bDebug) $this->launchException('No context defined');
      $result = null;
    }
    else {

      $result = $this->contexts->get($sName, $bDebug);
    }

    return $result;
  }

  public function addMessage($sMessage, array $aArguments = array()) {

    if (!$msg = $this->getContext('messages', false)) {

      $this->launchException("Cannot send message '$sMessage', context not ready");
    }

    $msg->add(array(
      'content' => $sMessage,
      'arguments' => $aArguments,
    ));
  }

  public function addElement($sName, Type $element) {

    $element->setHandler($this);
    $this->aElements[$sName] = $element;
  }

  protected function removeElement($sName) {

    unset($this->aElements[$sName]);
  }

  public function get($sPath, $bDebug = true) {

    return parent::get($sPath, $bDebug);
  }

  public function getElement($sName, $bDebug = true) {

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
    $aResult = array();

    foreach ($this->getElements() as $sName => $element) {

      if (!$element->validate()) $bValid = false;
      if ($element->isUsed()) $aResult[$sName] = $element;
    }

    if (!$bValid) {

      $this->addMessage('Some fields are missing or invalids, they have been highlighted');
    }

    $this->aElements = $aResult;

    return $bValid;
  }

  protected function buildInsert() {

    $aKeys = $aValues = array();

    foreach ($this->getElements() as $sName => $el) {

      $aValues[] = $el->escape();
      $aKeys[] = '`' . $sName . '`';
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

    $sResult = $this->getMode() === 'insert' ? $this->buildInsert() : $this->buildUpdate();

    if (!$this->isSub() && $this->getContext('messages', false)) {

      $this->addMessage('Datas has been ' . ($this->getMode() == 'insert' ? 'inserted' : 'updated'));
    }

    return $sResult;
  }
}

