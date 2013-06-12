<?php

namespace sylma\storage\sql\template\insert;
use sylma\core, sylma\dom, sylma\storage\sql, sylma\schema, sylma\parser\languages\common;

class Table extends sql\template\component\Table implements common\argumentable {

  protected $handler;
  protected $sMode = 'insert';

  public function parseRoot(dom\element $el) {

    parent::parseRoot($el);

    $this->setHandler($this->loadHandler());
  }

  public function addElementToHandler(sql\schema\element $el, $sDefault = '', $content = null) {

    $window = $this->getWindow();
    $arguments = $window->getVariable('post');

    $bOptional = $el->isOptional();
    $sName = $el->getAlias();

    $handler = $this->getHandler();

    $aArguments = array(
      'alias' => $sName,
      'title' => $el->getTitle(),
    );

    if ($bOptional) $aArguments['optional'] = true;
    if ($sDefault !== '') $aArguments['default'] = $sDefault;

    if (is_null($content)) {

      $content = $arguments->call('read', !$bOptional ? array($sName) : array($sName, false), 'php-string');
    }

    $call = $handler->call('addElement', array($sName, $el->buildReflector(array($content, $aArguments))));
    $window->add($call);

    //$content = $window->createCall($arguments, 'addMessage', 'php-bool', array(sprintf(self::MSG_MISSING, $this->getName())));
    //$test = $window->createCondition($window->createNot($var), $content);
    //$window->add($test);

    //$query->addSet($el, $handler->call('readElement', array($sName)));

  }

  protected function buildQuery() {

    $result = parent::buildQuery();
    $result->setHandler($this->getHandler());

    return $result;
  }

  protected function loadHandler() {

    $window = $this->getWindow();

    $result = $this->buildReflector(array($window->getVariable('arguments'), $window->getVariable('post'), $window->getVariable('contexts'), $this->getMode()));

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

