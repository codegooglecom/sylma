<?php

namespace sylma\storage\sql\template\insert;
use sylma\core, sylma\dom, sylma\storage\sql, sylma\parser\languages\common;

class Table extends sql\template\component\Table implements common\argumentable {

  protected $handler;
  protected $sMode = 'insert';

  public function parseRoot(dom\element $el) {

    $this->setHandler($this->loadHandler());
    parent::parseRoot($el);
  }

  protected function loadHandler() {

    $window = $this->getWindow();

    $result = $this->createObject('handler', array($window->getVariable('arguments'), $window->getVariable('contexts'), $this->getMode()), null, false);

    return $result->getVar();
  }

  public function getHandler() {

    return $this->handler;
  }

  protected function setHandler(common\_var $handler) {

    $this->handler = $handler;
  }

  public function asArgument() {

    $window = $this->getWindow();
    $result = $window->createCondition($this->getHandler()->call('validate'), $this->getParser()->getView()->addToResult(array($this->getQuery()->getCall()), false));

    return $result;
  }
}

