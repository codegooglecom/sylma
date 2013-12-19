<?php

namespace sylma\storage\sql\template\insert;
use sylma\core, sylma\dom, sylma\storage\sql, sylma\parser\languages\common;

class Table extends sql\template\component\Table implements common\argumentable {

  protected $handler;
  protected $sMode = 'insert';
  protected $bInsertQuery = false;
  protected $bElements = false;

  protected $aContent = array();
  protected $aValidates = array();

  public function parseRoot(dom\element $el) {

    parent::parseRoot($el);

    $this->setSource($this->getWindow()->getVariable('post'));
  }

  public function init($key = null, $parent = null) {

    $this->loadHandler($key, $parent);
  }

  public function getAlias($sMode = '') {

    return $this->getName();
  }

  public function addElement(sql\schema\element $el, $content = null, array $aArguments = array()) {

    if ($this->getHandler()) {

      $this->addElementToHandler($el, $content, $aArguments);
    }
    else {

      $this->getQuery()->addSet($el, $content);
    }

    $this->bElements = true;
  }

  protected function addElementToHandler(sql\schema\element $el, $content = null, array $aArguments = array()) {

    $sName = $el->getAlias();
    $handler = $this->getHandler(true);

    $aArguments = array_merge(array(
      'alias' => $sName,
      'title' => $el->getTitle(),
    ), $aArguments);

    array_filter($aArguments);

    if (is_null($content)) {

      $content = $this->getElementArgument($sName);
    }

    $call = $handler->call('addElement', array($sName, $el->buildReflector(array($content, $aArguments))));
    $this->addContent($call);


    //$content = $window->createCall($arguments, 'addMessage', 'php-bool', array(sprintf(self::MSG_MISSING, $this->getName())));
    //$test = $window->createCondition($window->createNot($var), $content);
    //$window->add($test);

    //$query->addSet($el, $handler->call('readElement', array($sName)));

  }

  public function getElementArgument($sName, $sMethod = 'read') {

    return $this->getSource()->call($sMethod, array($sName, false), 'php-string');
  }

  protected function buildQuery() {

    $result = parent::buildQuery();

    if ($this->getHandler()) {

      $result->setHandler($this->getHandler());
    }

    return $result;
  }

  protected function loadHandler($key = null, $parent = null) {

    $window = $this->getWindow();
    $view = $this->getParser()->getView();

    if ($key) {

      $this->setKey($key);
    }

    if (!$view->isInternal()) {

      $sToken = (string) $view->getRoot()->asPath();
      $token = $this->createObject('token', array($sToken), null, false);
    }
    else {

      $token = null;
    }

    $result = $this->buildReflector(array(
      $window->getVariable('arguments'),
      $window->getVariable('post'),
      $window->getVariable('contexts'),
      $this->getMode(),
      $token,
    ));

    $handler = $window->createVar($result);

    $this->setHandler($handler);
    $this->addContent($handler->getInsert());

    if ($this->isSub() && $this->getParent(false)) {

      $this->addContent($handler->call('setSub', array($this->getParent()->getAlias(), $key, $parent)));
    }
  }

  protected function getPosition() {

    return $this->getKey();
  }

  public function getHandler($bDebug = true) {

    if ($bDebug && !$this->handler) {

      $this->launchException('No handler defined');
    }

    return $this->handler;
  }

  public function setHandler(common\_var $handler) {

    $this->handler = $handler;
  }

  public function addTrigger(array $aContent) {

    $this->aTriggers[] = $aContent;
  }

  protected function useElements() {

    return $this->bElements;
  }

  protected function loadQuery() {

    $view = $this->getParser()->getView();
    $call = $this->getQuery()->getCall();

    if ($this->isSub()) {

      $result = $call->getInsert();
    }
    else {

      $result = $view->addToResult(array($call), false, true);
    }

    return $result;
  }

  protected function loadTriggers() {

    $window = $this->getWindow();

    $aContent[] = $this->loadQuery();

    if ($aTriggers = $this->getTriggers()) {

      $aContent[] = $window->createGroup($aTriggers);
    }

    return $aContent;
  }

  public function addContent($mVal) {

    $this->aContent[] = $mVal;
  }

  public function addValidate($mVal) {

    if ($this->aValidates) {

      $this->aValidates[] = $this->getWindow()->createOperator('&&');
    }

    $this->aValidates[] = $mVal;
  }

  public function callValidate() {

    $this->addValidate($this->getHandler()->call('validate'));

    return array_reverse($this->aValidates);
  }

  public function getValidation() {

    $result = $this->getWindow()->createGroup($this->getContent());
    $this->aContent = array();

    return $result;
  }

  public function getExecution() {

    $aResult = $this->aContent;
    $aResult[] = $this->loadTriggers();

    return $aResult;
  }

  protected function getContent() {
/*
    if ($this->getHandler()) {

      $aResult[] = $this->getHandler()->getInsert();
    }
*/
    $aResult[] = $this->aContent;

    return $aResult;
  }

  public function asArgument() {

    $window = $this->getWindow();
    $aContent = $this->getContent();
    $aContent[] = $window->createCondition($this->callValidate(), $this->loadTriggers());

    return $this->getWindow()->createGroup($aContent);
  }
}

