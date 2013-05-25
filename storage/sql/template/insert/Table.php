<?php

namespace sylma\storage\sql\template\insert;
use sylma\core, sylma\dom, sylma\storage\sql, sylma\schema, sylma\parser\languages\common;

class Table extends sql\template\component\Table implements common\argumentable {

  protected $handler;
  protected $sMode = 'insert';

  public function parseRoot(dom\element $el) {

    $this->setHandler($this->loadHandler());
    parent::parseRoot($el);
  }

  public function addElementToHandler(schema\parser\element $el, $sDefault = '') {

    $query = $this->getQuery();
    $window = $this->getWindow();
    $arguments = $window->getVariable('post');

    $type = $el->getType();
    $bOptional = $el->isOptional();
    $sName = $el->getAlias();

    $handler = $this->getHandler();
    $val = $arguments->call('read', !$bOptional ? array($sName) : array($sName, false), 'php-string');

    $aArguments = array(
      'alias' => $sName,
      'title' => $el->getName(),
    );

    if ($bOptional) $aArguments['optional'] = true;
    if ($sDefault) $aArguments['default'] = $sDefault;


    $call = $handler->call('addElement', array($sName, $type->instanciate($val, $aArguments)));
    $window->add($call);

    //$content = $window->createCall($arguments, 'addMessage', 'php-bool', array(sprintf(self::MSG_MISSING, $this->getName())));
    //$test = $window->createCondition($window->createNot($var), $content);
    //$window->add($test);

    $query->addSet($el, $handler->call('readElement', array($sName)));

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

