<?php

namespace sylma\parser\reflector\basic;
use \sylma\core, sylma\parser\languages\common, sylma\dom, sylma\parser\reflector;

/**
 * This class offers 2 main methods to load external parsers on dom element :
 * /@method loadElementForeign() and @method loadElementUnknown()
 * /@method parseElementForeign() and @method parseElementUknown() must be overrided to use them
 */
abstract class Foreigner extends Domed {

  protected $lastElement;

  protected $aAttributeParsers = array();

  /**
   * Sub parsers
   * @var array
   */
  protected $aParsers = array();

  abstract protected function lookupParserForeign($sNamespace);

  protected function parseRoot(dom\element $el) {

    return $this->parseElementSelf($el);
  }

  protected function parse(dom\node $node) {

    return $this->parseNode($node);
  }

  protected function parseElementForeign(dom\element $el) {

    $result = null;

    if ($this->allowForeign()) {

      $result = $this->loadElementForeign($el);
    }
    else {

      $result = $this->parseElementUnknown($el);
    }

    return $result;
  }

  protected function loadElementForeign(dom\element $el) {

    if ($parser = $this->lookupParserForeign($el->getNamespace())) {

      $mResult = $this->loadElementForeignKnown($el, $parser);
    }
    else {

      $mResult = $this->parseElementUnknown($el);
    }

    return $mResult;
  }

  abstract protected function loadElementForeignKnown(dom\element $el, reflector\elemented $parser);

  protected function validateParser($sNamespace, $sParser = 'element') {

    $result = $this->lookupParserForeign($sNamespace);

    if ($result) {

      $bValid = false;

      switch ($sParser) {

        case 'element' : $bValid = $result instanceof reflector\elemented; break;
        case 'attribute' : $bValid = $result instanceof reflector\attributed; break;
      }

      if (!$bValid) {

        $this->throwException(sprintf('Cannot use parser %s in %s context', get_class($result), $sParser));
      }
    }

    return $result;
  }

  protected function parseElementUnknown(dom\element $el) {

    if ($this->allowUnknown()) {

      $result = $this->loadElementUnknown($el);
    }
    else {

      $result = parent::parseElementUnknown($el);
    }

    return $result;
  }

  /**
   * Build a new element from the source element, cannot copy cause of following steps
   *
   * - Check for foreign attributes.
   *   They define result if exists with @method parseAttributesForeign([new element]) (1st pass)
   * - Add parsed children to new element.
   * - Inform foreign attributes parser that element has been parsed with @method $parser->onClose
   *   This allow parsers to edit new element (2nd pass)
   *
   * These steps give the ability to attribute parsers to return element into new container
   *
   * @param dom\element $el
   * @return dom\element|mixed The new element or, if foreign attributes exists, result of parsing, so if result
   *         is changed on 1st pass and changes happened to new element on 2nd pass, they will we be ignored.
   */
  protected function loadElementUnknown(dom\element $el) {

    $newElement = $this->createElement($el->getName(), null, array(), $el->getNamespace());

    if ($this->useForeignAttributes($el)) {

      $aForeigns = $this->getForeignAttributes($el, $newElement);
      $mResult = $this->parseAttributesForeign($el, $newElement, $aForeigns);

    }
    else {

      foreach ($el->getAttributes() as $attr) {

        $newElement->add($this->parseAttribute($attr));
      }

      $mResult = $newElement;
    }

    $aParsers = $this->getAttributeParsers();
    $this->setAttributeParsers();

    //$this->startElement($newElement);

    if ($aChildren = $this->parseChildren($el->getChildren())) {

      $newElement->add($aChildren);
    }

    foreach ($aParsers as $parser) {

      $parser->onClose($el, $newElement);
    }

    //$this->stopElement();

    return $mResult;
  }

  protected function getForeignAttributes(dom\element $source, dom\element $target = null, $bRemove = false) {

    $aResult = array();

    foreach ($source->getAttributes() as $attr) {

      $sNamespace = $attr->getNamespace();

      if (!$sNamespace || $sNamespace == $this->getNamespace()) {

        if ($target) $target->add($this->parseAttribute($attr));
      }
      else {

        $aResult[$sNamespace] = true;
        if ($bRemove) $attr->remove();
      }
    }

    return $aResult;
  }

  /**
   * Clone then clean the element from self namespaced attributes
   *
   * @param $el
   * @return dom\element
   */
  protected function cleanAttributes(dom\element $el) {

    $doc = $this->createDocument($el);
    $root = $doc->getRoot();

    foreach ($root->getAttributes() as $attr) {

      $sNamespace = $attr->getNamespace();

      if ($sNamespace && $this->useNamespace($sNamespace)) {

        $attr->remove();
      }
    }

    return $root;
  }

  /**
   * @param dom\element $el
   * @return dom\element|common\_scope
   */
  protected function parseAttributesForeign(dom\element $el, $content, array $aForeigns) {

    $aParsers = array();

    foreach ($aForeigns as $sNamespace => $bVal) {

      $parser = $this->validateParser($sNamespace, 'attribute');

      if ($parser) {

        $aParsers[] = $parser;
        $parser->init();
        //$parser->setParent($this);
        $content = $parser->parseAttributes($el, $content, $content);
      }
      else {

        $this->throwException(sprintf('Unknown attribute with @namespace %s in %s', $sNamespace, $el->asToken()));
      }
    }

    $this->setAttributeParsers($aParsers);

    return $content;
  }

  protected function getAttributeParsers() {

    return $this->aAttributeParsers;
  }

  protected function setAttributeParsers(array $aParsers = array()) {

    $this->aAttributeParsers = $aParsers;
  }
}

