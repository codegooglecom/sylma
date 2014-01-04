<?php

namespace sylma\schema\xsd;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\schema, sylma\storage\fs;

/**
 * DOM delayed class. This class won't parse all DOM at start, but lookup for
 * usefull elements when requested
 */
class Elemented extends schema\parser\Handler implements reflector\elemented, schema\parser\schema {

  const NS = 'http://www.w3.org/2001/XMLSchema';
  const PREFIX = 'xs';

  const SSD_NS = 'http://2013.sylma.org/schema/ssd';
  const SSD_PREFIX = 'ssd';
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

    if ($el->getName() != 'schema') {

      $this->throwException(sprintf('Bad root %s', $el->asToken()));
    }

    $doc = $el->getHandler();

    $this->setDocument($doc);

    $this->loadTargetNamespace($doc);
    $this->loadDefaultNamespace($doc);

    $this->setDocument($this->createDocument($doc));
    $this->getDocument()->getRoot()->set();
    $this->addSchema($doc);

    $this->loadBaseTypes(array(
      'string' => self::NS,
      'integer' => self::NS,
      'float' => self::NS,
      'boolean' => self::NS,
    ));

    $this->setDirectory(__FILE__);

    $import = $this->loadSimpleComponent('component/import');
    $import->parseFile($this->getFile(self::SSD_TYPES));

    $this->setNamespace(self::SSD_NS, self::SSD_PREFIX, false);
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
  public function getElement($sName = '', $sNamespace = '', $bDebug = true) {

    //list($sNamespace, $sName) = $this->parseName($sName, $context);
    if (!$sNamespace) $sNamespace = $this->getTargetNamespace();

    if (!$sName or !$result = $this->loadElement($sName, $sNamespace)) {

      if ($el = $this->lookupElement($sName, $sNamespace, $bDebug)) {

        $result = $this->parseComponent($el);

        $result->loadNamespace($sNamespace);
        $this->addElement($result);
      }
      else {

        $result = null;
      }
    }

    return $result;
  }

  public function getElements() {

    $this->launchException('Not yet implemented');
  }

  protected function lookupElement($sName, $sNamespace, $bDebug = true) {

    if (!count($this->aElementsNodes)) {

      $this->throwException('No element loaded');
    }

    if (!$sName) {

      $sName = key(current($this->aElementsNodes));
    }

    if (!isset($this->aElementsNodes[$sNamespace][$sName])) {

      if ($bDebug) $this->throwException(sprintf('Cannot find element %s:%s', $sNamespace, $sName));
      $result = null;
    }
    else {

      $result = $this->aElementsNodes[$sNamespace][$sName];
    }

    return $result;
  }

  /**
   *
   * @param string $sName
   * @param boolean $bDebug
   * @return type\Basic
   */
  public function getType($sName, $sNamespace, $bDebug = true) {

    //list($sNamespace, $sName) = $this->parseName($sName, $source, $context);

    if (!$result = $this->loadType($sName, $sNamespace)) {

      if ($el = $this->lookupType($sName, $sNamespace, $bDebug)) {

        $result = $this->parseComponent($el);
        $result->setNamespace($sNamespace, self::TYPE_PREFIX);

        $this->addType($result);
      }
    }

    return $result;
  }

  protected function lookupType($sName, $sNamespace, $bDebug = true) {

    if (!isset($this->aTypesNodes[$sNamespace][$sName])) {

      if ($bDebug) $this->launchException("Cannot find type $sNamespace:$sName");
      $result = null;
    }
    else {

      $result = $this->aTypesNodes[$sNamespace][$sName];
    }

    return $result;
  }

  protected function loadDefaultNamespace(dom\document $doc) {

    $this->setDefaultNamespace($doc->getRoot()->lookupNamespace());
  }

  public function addSchema(dom\document $doc, fs\file $file = null) {

    if ($file) {

      $this->getRoot()->importDocument($doc, $file);
    }

    $sNamespace = $this->parseTargetNamespace($doc);

    $sResult = $this->browseSchemaChild($doc->getRoot(), $sNamespace);

    return $sResult;
  }

  protected function addSchemaChild(dom\element $el, $sNamespace) {

    $sName = '';

    switch ($el->getName()) {

      case 'element' :

        $sName = $this->addSchemaElement($el, $sNamespace);
        //$this->browseSchemaChild($el, $sNamespace);
        break;

      case 'complexType' :
      case 'simpleType' :

        $sName = $this->addSchemaType($el, $sNamespace);
        break;

      default :

        $this->getDocument()->add($el);
    }

    return $sName;
  }

  protected function browseSchemaChild(dom\element $parent, $sNamespace) {

    $sResult = '';

    foreach ($parent->getChildren() as $child) {

      if ($child->getType() !== $child::COMMENT) {

        $sName = $this->addSchemaChild($child, $sNamespace);

        if (!$sResult && $sName) {

          $sResult = $sName;
        }
      }
    }

    return $sResult;
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

    return $sName;
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

