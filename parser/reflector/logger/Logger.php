<?php

namespace sylma\parser\reflector\logger;
use sylma\core, sylma\dom;

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

  protected function show($mVar) {

    $result = $this->createDocument();
    $root = $result->addElement('ul', null, array(), \Sylma::read('namespaces/html'));

    if (is_array($mVar)) {

      foreach ($mVar as $item) {

        $root->addElement('li', $this->parseString($item));
      }
    }
    else if (is_string($mVar)) {

      $root->addElement('li', $this->parseString($mVar));
    }
    else {

      $this->launchException('Cannot show var', get_defined_vars());
    }

    return $result;
  }

  protected function parseString($sValue) {

    $formater = $this->getManager('formater');

    return $formater->stringToDOM($formater->parseTokens($sValue));
  }

  public function asMessage() {

    $context = $this->getManager(self::PARSER_MANAGER)->getContext('errors');
    $doc = $this->getRoot()->asDOM();

    $result = $this->getTemplate('components.xsl')->parseDocument($doc);

    $cleaner = new \sylma\modules\html\Cleaner();
    $context->add(array('content' => $cleaner->clean($result)));
  }
}

