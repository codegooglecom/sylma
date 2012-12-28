<?php

namespace sylma\parser\reflector\basic;
use \sylma\core, sylma\parser\languages\common, sylma\dom, sylma\parser;

/**
 * This class offers 2 main methods to load external parsers on dom element :
 * /@method loadElementForeign() and @method loadElementUnknown()
 * /@method parseElementForeign() and @method parseElementUknown() must be overrided to use them
 */
abstract class Master extends Domed {

  /**
   * Sub parsers
   * @var array
   */
  protected $aParsers = array();

  protected $foreignElements;
  protected $aAttributeParsers = array();
  protected $lastElement;

  /**
   *
   * @param string $sNamespace
   * @return parser\domed
   */
  public function getParser($sNamespace) {

    $result = null;

    if (array_key_exists($sNamespace, $this->aParsers)) {

      $result = $this->aParsers[$sNamespace];
    }
    else {

      $manager = $this->getControler('parser');
      $result = $manager->getParser($sNamespace, $this, false);

      if ($result) $this->setParser($result, $result->getNS());
    }

    if ($result) $result->setParent($this);

    return $result;
  }

  /**
   * Set local parsers, with associated namespaces
   * @param parser\reflector\domed $parser
   * @param array $aNS
   */
  public function setParser(parser\reflector\domed $parser, array $aNS) {

    $aResult = array();

    foreach ($aNS as $sNamespace) {

      $aResult[$sNamespace] = $parser;
    }

    $this->aParsers = array_merge($this->aParsers, $aResult);
  }

  protected function loadParser($sNamespace, $sParser = 'element') {

    $result = $this->getParser($sNamespace);

    if ($result) {

      $bValid = false;

      switch ($sParser) {

        case 'element' : $bValid = $result instanceof parser\reflector\elemented; break;
        case 'attribute' : $bValid = $result instanceof parser\reflector\attributed; break;
      }

      if (!$bValid) {

        $this->throwException(sprintf('Cannot use parser %s in %s context', $sNamespace, $sParser));
      }
    }

    return $result;
  }

  public function parse(dom\node $node) {

    return $this->parseNode($node);
  }

  protected function createElement($sName, $mContent = null, array $aAttributes = array(), $sNamespace = '') {

    if (!$sNamespace) {

      $this->throwException('Element without namespace vorbidden');
    }

    if (!$this->foreignElements) {

      $this->foreignElements = $this->getControler('dom')->createDocument();
      $this->foreignElements->addElement('root');
    }

    $el = $this->foreignElements->createElement($sName, $mContent, $aAttributes, $sNamespace);

    return $el;
  }

  protected function loadElementForeign(dom\element $el) {

    if ($parser = $this->loadParser($el->getNamespace(), 'element')) {

      $mResult = $parser->parseRoot($el);
    }
    else {

      $mResult = $this->parseElementUnknown($el);
    }

    return $mResult;

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

      $mResult = $this->parseAttributesForeign($el, $newElement);
    }
    else {

      foreach ($el->getAttributes() as $attr) {

        $newElement->add($this->parseAttribute($attr));
      }

      $mResult = $newElement;
    }

    $aParsers = $this->getAttributeParsers();
    $this->setAttributeParsers();

    $this->setLastElement($newElement);

    if ($aChildren = $this->parseChildren($el->getChildren())) {

      $newElement->add($aChildren);
    }

    foreach ($aParsers as $parser) {

      $parser->onClose($el, $newElement);
    }

    return $mResult;
  }

  /**
   *
   * @param dom\element $el
   * @return dom\element|common\_scope
   */
  protected function parseAttributesForeign(dom\element $el, dom\element $newElement) {

    $aForeigns = array();

    foreach ($el->getAttributes() as $attr) {

      $sNamespace = $attr->getNamespace();

      if (!$sNamespace || $sNamespace == $this->getNamespace()) {

        $newElement->add($this->parseAttribute($attr));
      }
      else {

        $aForeigns[$sNamespace] = true;
      }
    }

    $mResult = $newElement;

    $aParsers = array();

    foreach ($aForeigns as $sNamespace => $bVal) {

      $parser = $this->loadParser($sNamespace, 'attribute');

      if ($parser) {

        $aParsers[] = $parser;
        $mResult = $parser->parseAttributes($el, $newElement, $mResult);
      }
      else {

        $this->throwException(sprintf('Unknown attribute with @namespace %s in %s', $sNamespace, $el->asToken()));
      }
    }

    $this->setAttributeParsers($aParsers);

    return $mResult;
  }

  protected function getAttributeParsers() {

    return $this->aAttributeParsers;
  }

  protected function setAttributeParsers(array $aParsers = array()) {

    $this->aAttributeParsers = $aParsers;
  }
}
