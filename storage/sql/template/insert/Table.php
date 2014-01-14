<?php

namespace sylma\storage\sql\template\insert;
use sylma\core, sylma\dom, sylma\storage\sql, sylma\parser\languages\common;

class Table extends sql\template\component\Table implements common\argumentable {

  protected $dummy;
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

    $this->loadDummy($key, $parent);
  }

  public function getAlias($sMode = '') {

    return $this->getName();
  }

  public function addElement(sql\schema\element $el, $content = null, array $aArguments = array()) {

    if ($this->getDummy()) {

      $this->addElementToDummy($el, $content, $aArguments);
    }
    else {

      $this->getQuery()->addSet($el, $content);
    }

    $this->bElements = true;
  }

  protected function addElementToDummy(sql\schema\element $el, $content = null, array $aArguments = array()) {

    $sName = $el->getAlias();
    $handler = $this->getDummy(true);

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

    if ($this->getDummy()) {

      $result->setHandler($this->getDummy());
    }

    return $result;
  }

  protected function loadDummy($key = null, $parent = null) {

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

    $aArguments = array(
      $window->getVariable('arguments'),
      $window->getVariable('post'),
      $window->getVariable('contexts'),
      $this->getMode(),
      $token,
    );

    if ($this->isSub()) {

      $form = $this->buildReflector($aArguments, 'sub');
    }
    else {

      $form = $this->buildReflector($aArguments);
    }

    $var = $window->createVar($form);

    $this->setDummy($var);
    $this->addContent($var->getInsert());

    if ($this->isSub() && $this->getParent(false)) {

      $id = $this->getElement('id', null, false);
      $this->addContent($var->call('setSub', array($this->getParent()->getAlias(), $key, $parent, $id ? $id->getName() : null)));
    }
  }

  protected function getPosition() {

    return $this->getKey();
  }

  /**
   * @usedby Foreign::buildMultiple()
   * @usedby Reference::reflectFunctionRef()
   * @return common\_var
   */
  public function getDummy($bDebug = true) {

    if ($bDebug && !$this->dummy) {

      $this->launchException('No dummy defined');
    }

    return $this->dummy;
  }

  /**
   * @usedby Reference::reflectFunctionRef()
   */
  public function setDummy(common\_var $handler) {

    $this->dummy = $handler;
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

    $this->addValidate($this->getDummy()->call('validate'));

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

