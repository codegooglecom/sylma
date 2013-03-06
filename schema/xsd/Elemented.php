<?php

namespace sylma\schema\xsd;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\schema\parser;

/**
 * DOM delayed class. This class won't parse all DOM at start, but lookup for
 * usefull elements when requested
 */
class Elemented extends parser\Handler implements reflector\elemented, parser\schema {

  const NS = 'http://www.w3.org/2001/XMLSchema';

  /**
   * These two vars are use for dom cache and namespaced names
   */
  protected $aElementsNodes = array();
  protected $aTypesNodes = array();

  protected $aElements = array();
  protected $aTypes = array();

  protected $element;

  public function parseRoot(dom\element $el) {

    //$this->setNode($el);

    if (!$el->getName() == 'schema') {

      $this->throwException(sprintf('Bad root %s', $el->asToken()));
    }

    $doc = $el->getHandler();

    $this->setDocument($doc);

    $this->loadTargetNamespace($doc);
    $this->loadDefaultNamespace($doc);

    $this->setDocument($this->createDocument('schema'));
    $this->addSchema($doc);

    parent::parseRoot($el);
  }

  protected function loadTargetNamespace(dom\document $doc) {

    $sNamespace = $doc->readx('@targetNamespace');
    $this->setNamespace($sNamespace, 'target');
  }

  protected function parseName($sName, dom\element $context = null) {

    if (!$context) $context = $this->getDocument()->getRoot();

    $iPrefix = strpos($sName, ':');

    if ($iPrefix !== false) {

      $sNamespace = $context->getNamespace(substr($sName, 0, $iPrefix));
      $sName = substr($sName, $iPrefix + 1);
    }
    else {

      $sNamespace = $this->getDefaultNamespace();
    }

    return array($sNamespace, $sName);
  }
  /**
   *
   * @param string $sName
   * @param boolean $bDebug
   * @return type\Basic
   */
  public function getElement($sName = '', dom\element $context = null) {

    list($sNamespace, $sName) = $this->parseName($sName, $context);

    if (!$sName or !$result = $this->loadElement($sName, $sNamespace)) {

      $el = $this->lookupElement($sName, $sNamespace);
      $result = $this->addElement($this->parseComponent($el));
    }

    return $result;
  }

  protected function lookupElement($sName, $sNamespace) {

    if (!count($this->aElementsNodes)) {

      $this->throwException('No element loaded');
    }

    if (!$sName) {

      $sName = key(current($this->aElementsNodes));
    }

    if (!isset($this->aElementsNodes[$sNamespace][$sName])) {

      $this->throwException(sprintf('Cannot find element %s:%s', $sNamespace, $sName));
    }

    return $this->aElementsNodes[$sNamespace][$sName];
  }

  /**
   *
   * @param string $sName
   * @param boolean $bDebug
   * @return type\Basic
   */
  public function getType($sName, dom\element $context = null) {

    list($sNamespace, $sName) = $this->parseName($sName, $context);

    if (!$result = $this->loadType($sName, $sNamespace)) {

      $el = $this->lookupType($sName, $sNamespace);
      $result = $this->addType($this->parseComponent($el));
    }

    return $result;
  }

  protected function lookupType($sName, $sNamespace) {

    if (!isset($this->aTypesNodes[$sNamespace][$sName])) {

      $this->throwException(sprintf('Cannot find type %s', $sName));
    }

    return $this->aTypesNodes[$sNamespace][$sName];
  }

  protected function loadDefaultNamespace(dom\document $doc) {

    $this->setDefaultNamespace($doc->getRoot()->lookupNamespace());
  }

  public function addSchema(dom\document $doc) {

    foreach ($doc->getChildren() as $child) {

      $this->addSchemaChild($child);
    }
  }

  protected function addSchemaChild(dom\element $el) {

    switch ($el->getName()) {

      case 'element' :
      case 'table' :

        $this->addSchemaElement($el);
        break;

      case 'complexType' :
      case 'simpleType' :

        $this->addSchemaType($el);
        break;

      default :

        $this->getDocument()->add($el);
    }
  }

  protected function addSchemaElement(dom\element $el) {

    $sName = $el->readx('@name');
    $sNamespace = $el->lookupNamespace();

    if (!isset($this->aElementsNodes[$sNamespace])) {

      $this->aElementsNodes[$sNamespace] = array();
    }

    if (isset($this->aElementsNodes[$sNamespace][$sName])) {

      $this->throwException(sprintf('Element "%s" already exists', $sNamespace . ':' . $sName), $el->asToken());
    }

    $this->aElementsNodes[$sNamespace][$sName] = $this->getDocument()->add($el);
  }

  protected function addSchemaType(dom\element $el) {

    list($sNamespace, $sName) = $this->parseName($el->readx('@name'));

    if (!isset($this->aTypesNodes[$sNamespace])) {

      $this->aTypesNodes[$sNamespace] = array();
    }

    if (isset($this->aTypesNodes[$sNamespace][$sName])) {

      $this->throwException(sprintf('Type "%s" already exists', $sNamespace . ':' . $sName), $el->asToken());
    }

    $this->aTypesNodes[$sNamespace][$sName] = $this->getDocument()->add($el);
  }
}

