<?php

namespace sylma\schema\xsd;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\schema;

/**
 * DOM delayed class. This class won't parse all DOM at start, but lookup for
 * usefull elements when requested
 */
class Elemented extends schema\parser\Handler implements reflector\elemented, schema\parser\schema {

  const NS = 'http://www.w3.org/2001/XMLSchema';
  const TARGET_PREFIX = 'target';

  const SSD_TYPES = '../ssd/simple.xsd';

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

    $this->setDocument($this->createDocument($doc));
    $this->getDocument()->getRoot()->set();
    $this->addSchema($doc);

    $this->loadBaseTypes(array('string' => self::NS, 'integer' => self::NS));

    $this->setDirectory(__FILE__);

    $import = $this->loadSimpleComponent('component/import');
    $import->parseFile($this->getFile(self::SSD_TYPES));
  }

  protected function lookupNamespace($sPrefix, dom\element $context = null) {

    $sNamespace = '';

    if ($context) {

      if (!$sNamespace = $context->lookupNamespace($sPrefix)) {

        $sNamespace = $context->getHandler()->getRoot()->lookupNamespace($sPrefix);
      }
    }

    if (!$sNamespace) {

      $sNamespace = $this->getDocument()->getRoot()->lookupNamespace($sPrefix);
    }

    return $sNamespace ? $sNamespace : $this->getNamespace($sPrefix);
  }

  public function getTargetNamespace() {

    return $this->getNamespace('target');
  }

  protected function loadTargetNamespace(dom\document $doc) {

    $this->setNamespace($this->parseTargetNamespace($doc), self::TARGET_PREFIX);

  }

  protected function parseTargetNamespace(dom\document $doc) {

    return $doc->readx('@targetNamespace');
  }

  /**
   * @param string $sName ([prefix]:)?[name]
   * @param $source
   * @param $context
   * @return type
   */
  public function parseName($sName, schema\parser\namespaced $source = null, dom\element $context = null) {

    $iPrefix = strpos($sName, ':');

    if ($iPrefix !== false) {

      $sPrefix = substr($sName, 0, $iPrefix);
      $sNamespace = $this->lookupNamespace($sPrefix, $context);

      $sName = substr($sName, $iPrefix + 1);
    }
    else {

      $sNamespace = $source && $source->getNamespace() ? $source->getNamespace() : $this->getTargetNamespace();
    }

    return array($sNamespace, $sName);
  }

  /**
   *
   * @param string $sName
   * @param boolean $bDebug
   * @return type\Basic
   */
  public function getElement($sName = '', $sNamespace = '') {

    //list($sNamespace, $sName) = $this->parseName($sName, $context);
    if (!$sNamespace) $sNamespace = $this->getTargetNamespace();

    if (!$sName or !$result = $this->loadElement($sName, $sNamespace)) {

      $el = $this->lookupElement($sName, $sNamespace);

      $result = $this->parseComponent($el);

      $result->loadNamespace($sNamespace);
      $this->addElement($result);
    }

    return $result;
  }

  public function getElements() {

    $this->launchException('Not yet implemented');
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
  public function getType($sName, $sNamespace) {

    //list($sNamespace, $sName) = $this->parseName($sName, $source, $context);

    if (!$result = $this->loadType($sName, $sNamespace)) {

      $el = $this->lookupType($sName, $sNamespace);
      $result = $this->addType($this->parseComponent($el));
      $result->setNamespace($sNamespace);
    }

    return $result;
  }

  protected function lookupType($sName, $sNamespace) {

    if (!isset($this->aTypesNodes[$sNamespace][$sName])) {

      $this->throwException(sprintf('Cannot find type %s:%s', $sNamespace, $sName));
    }

    return $this->aTypesNodes[$sNamespace][$sName];
  }

  protected function loadDefaultNamespace(dom\document $doc) {

    $this->setDefaultNamespace($doc->getRoot()->lookupNamespace());
  }

  public function addSchema(dom\document $doc) {

    $sNamespace = $this->parseTargetNamespace($doc);

    foreach ($doc->getChildren() as $child) {

      $this->addSchemaChild($child, $sNamespace);
    }
  }

  protected function addSchemaChild(dom\element $el, $sNamespace) {

    switch ($el->getName()) {

      case 'element' :

        $this->addSchemaElement($el, $sNamespace);
        break;

      case 'complexType' :
      case 'simpleType' :

        $this->addSchemaType($el, $sNamespace);
        break;

      default :

        $this->getDocument()->add($el);
    }
  }

  protected function addSchemaElement(dom\element $el, $sNamespace) {

    $sName = $el->readx('@name');

    if (!isset($this->aElementsNodes[$sNamespace])) {

      $this->aElementsNodes[$sNamespace] = array();
    }

    if (isset($this->aElementsNodes[$sNamespace][$sName])) {

      $this->throwException(sprintf('Element "%s" already exists', $sNamespace . ':' . $sName), $el->asToken());
    }

    $this->aElementsNodes[$sNamespace][$sName] = $this->getDocument()->add($el);
  }

  protected function addSchemaType(dom\element $el, $sNamespace) {

    $sName = $el->readx('@name');

    if (!isset($this->aTypesNodes[$sNamespace])) {

      $this->aTypesNodes[$sNamespace] = array();
    }

    if (isset($this->aTypesNodes[$sNamespace][$sName])) {

      $this->throwException(sprintf('Type "%s" already exists', $sNamespace . ':' . $sName), $el->asToken());
    }

    $this->aTypesNodes[$sNamespace][$sName] = $this->getDocument()->add($el);
  }
}

