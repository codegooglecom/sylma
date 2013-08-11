<?php

namespace sylma\schema\cached\form;
use sylma\core;

class Form extends core\module\Argumented {

  protected $sMode;
  protected $sName;
  protected $iKey;
  protected $contexts;
  protected $aElements = array();
  protected $bSub = false;
  protected $bValid = true;

  public function __construct(core\argument $arguments, core\argument $post, core\argument $contexts, $sMode, Token $token = null) {

    if ($token) {

      $token->isValid();
    }

    $this->setMode($sMode);

    $this->setArguments($arguments);
    $this->setContexts($contexts);
    $this->setSettings($post);
  }

  protected function checkToken($sPath) {


  }

  public function setSub($sAlias, $iKey, $parent) {

    $this->isSub(true);

    $this->setName($sAlias);
    $this->setKey($iKey);
    $this->setParent($parent);
  }

  protected function isSub($bVal = null) {

    if (is_bool($bVal)) $this->bSub = $bVal;

    return $this->bSub;
  }

  protected function setName($sName) {

    $this->sName = $sName;
  }

  public function getName() {

    return $this->sName;
  }

  protected function setKey($iKey) {

    $this->iKey = $iKey;
  }

  public function getKey() {

    return $this->iKey;
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

  protected function setParent(self $parent) {

    $this->parent = $parent;
  }

  protected function getParent() {

    return $this->parent;
  }

  public function addMessage($sMessage, array $aArguments = array()) {

    if ($this->isSub()) {

      $this->getParent()->addMessage($sMessage, $aArguments);
    }
    else {

      if (!$msg = $this->getContext('messages', false)) {

        $this->launchException("Cannot send message '$sMessage', context not ready");
      }

      $msg->add(array(
        'content' => $sMessage,
        'arguments' => $aArguments,
      ));
    }
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

      if ($this->isSub()) {

        $this->getParent()->isValid(false);
      }
      else {

        if (!$bValid || !$this->isValid()) {

          $this->addMessage('Some fields are missing or invalids, they have been highlighted');
        }
      }
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

  protected function isValid($bVal = null) {

    if (is_bool($bVal)) $this->bValid = $bVal;

    return $this->bValid;
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

