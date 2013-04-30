<?php

namespace sylma\template\parser;
use sylma\core;

class Logger extends core\module\Domed {

  const NS = 'http://2013.sylma.org/template/parser';

  protected $root;
  protected $aComponents = array();

  public function __construct() {

    $this->setDirectory(__FILE__);
    $this->loadDefaultArguments();
    $this->setNamespace(self::NS);
    $this->loadRoot();
  }

  protected function loadRoot() {

    $this->root = $this->createArgument(array('log' => array('#component' => array())));
  }

  protected function getRoot() {

    return $this->root;
  }

  public function startComponent($component, $sMessage, array $aVars = array()) {

    if ($this->aComponents) {

      $parent = end($this->aComponents)->get('#component');
    }
    else {

      $parent = $this->getRoot()->get('log/#component');
    }

    $this->aComponents[] = $parent->add(array(
      'message' => $sMessage,
      'class' => get_class($component),
      'vars' => $aVars ? $this->show($aVars, false) : null,
      '#component' => array(),
    ), true);
  }

  public function stopComponent() {

    array_pop($this->aComponents);
  }

  public function addException($sMessage) {

    if ($last = end($this->aComponents)) $last->set('exception', $sMessage);
  }

  public function asMessage() {

    $context = $this->getManager(self::PARSER_MANAGER)->getContext('messages');
    //$context->add(current($this->aComponents)->asDOM());
    //$test = $this->createArgument($arg->asArray(true));

    $doc = $this->getRoot()->asDOM();
//dsp($arg);
//dsp($doc);
    $result = $this->getTemplate('components.xsl')->parseDocument($doc);
    $context->add($result);
  }
}

