<?php

namespace sylma\schema\cached\form;
use sylma\core;

class Form extends core\module\Domed {

  protected $sMode;
  protected $contexts;
  protected $aElements = array();
  protected $bValid = true;
  protected $sName;

  public function __construct(core\argument $arguments, core\argument $post, core\argument $contexts, $sMode, Token $token = null) {

    //$this->validateToken($token);

    $this->setMode($sMode);

    $this->setArguments($arguments);
    $this->setContexts($contexts);
    $this->setSettings($post);
  }

  protected function validateToken(Token $token = null) {

    if ($token) {

      $token->isValid();
    }
  }

  protected function setName($sName) {

    $this->sName = $sName;
  }

  public function getName() {

    return $this->sName;
  }

  protected function setMode($sMode) {

    $this->sMode = $sMode;
  }

  public function getMode() {

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

    $mResult = null;

    if (!isset($this->aElements[$sName])) {

      if ($bDebug) $this->launchException("Element $sName does not exists");
    }
    else {

      $mResult = $this->aElements[$sName];
    }

    return $mResult;
  }

  protected function getElements() {

    return $this->aElements;
  }

  protected function validateElements() {

    $bResult = true;
    $aResult = array();

    foreach ($this->getElements() as $sName => $element) {

      if (!$element->validate()) $bResult = false;
      if ($element->isUsed()) $aResult[$sName] = $element;
    }

    $this->aElements = $aResult;

    return $bResult;
  }

  public function validate() {

    $bResult = $this->validateElements();

    if (!$bResult || !$this->isValid()) {

      $this->addMessage('Some fields are missing or invalids, they have been highlighted');
    }

    return $bResult;
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

    if (!$this->getElements()) {

      $this->launchException('Cannot update table without registered field');
    }

    $aResult = array();

    foreach ($this->getElements() as $sName => $el) {

      $aResult[] = '`' . $sName . '`' . '=' . $el->escape();
    }

    return implode(',', $aResult);
  }

  protected function isValid($bVal = null) {

    if (is_bool($bVal)) $this->bValid = $bVal;

    return $this->bValid;
  }

  public function asString() {

    $sResult = $this->getMode() === 'insert' ? $this->buildInsert() : $this->buildUpdate();

    if ($this->getContext('messages', false)) {

      $this->addMessage('Datas has been ' . ($this->getMode() == 'insert' ? 'inserted' : 'updated'));
    }

    return $sResult;
  }
}

