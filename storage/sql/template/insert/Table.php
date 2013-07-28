<?php

namespace sylma\storage\sql\template\insert;
use sylma\core, sylma\dom, sylma\storage\sql, sylma\parser\languages\common;

class Table extends sql\template\component\Table implements common\argumentable {

  protected $handler;
  protected $sMode = 'insert';
  protected $bInsertQuery = false;
  protected $bElements = false;

  protected $aContent = array();

  public function parseRoot(dom\element $el) {

    parent::parseRoot($el);
    $this->loadHandler();

    $this->setSource($this->getWindow()->getVariable('post'));
  }

  public function getAlias($sMode = '') {

    $this->getName();
  }

  public function addElement(sql\schema\element $el, $sDefault = '', $content = null) {

    if ($this->getHandler()) {

      $this->addElementToHandler($el, $sDefault, $content);
    }
    else {

      $this->getQuery()->addSet($el, $content);
    }

    $this->bElements = true;
  }

  protected function addElementToHandler(sql\schema\element $el, $sDefault = '', $content = null) {

    $sName = $el->getAlias();
    $handler = $this->getHandler();

    $aArguments = array(
      'alias' => $sName,
      'title' => $el->getTitle(),
    );

    if ($el->isOptional()) $aArguments['optional'] = true;
    if ($sDefault !== '') $aArguments['default'] = $sDefault;

    if (is_null($content)) {

      $content = $this->getElementArgument($sName);
    }

    $call = $handler->call('addElement', array($sName, $el->buildReflector(array($content, $aArguments))));
    $this->aContent[] = $call;


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

  public function loadHandler() {

    $window = $this->getWindow();
    $view = $this->getParser()->getView();

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

    $this->setHandler($window->createVar($result));
  }

  public function getHandler() {

    return $this->handler;
  }

  protected function setHandler(common\_var $handler) {

    $this->handler = $handler;
  }

  public function addTrigger(array $aContent) {

    $this->aTriggers[] = $aContent;
  }

  protected function useElements() {

    return $this->bElements;
  }

  protected function loadQuery() {

    if (!$this->useElements()) {

      $this->launchException('Cannot insert no element');
    }

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

    return $window->createCondition($this->getHandler()->call('validate'), $aContent);
  }

  public function asArgument() {

    if ($this->getHandler()) {

      $aResult[] = $this->getHandler()->getInsert();
    }

    $aResult[] = $this->aContent;
    $aResult[] = $this->loadTriggers();

    return $this->getWindow()->createGroup($aResult);
  }
}

