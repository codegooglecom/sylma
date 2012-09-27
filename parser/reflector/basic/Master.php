<?php

namespace sylma\parser\reflector\basic;
use \sylma\core, sylma\parser\languages\common, sylma\dom, sylma\parser;

\Sylma::load('Domed.php', __DIR__);

abstract class Master extends Domed {

  /**
   * Sub parsers
   * @var array
   */
  protected $aParsers = array();

  protected $foreignElements;

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
      $this->foreignElements->addElement('root', null, array(), $this->getNamespace());
    }

    $el = $this->foreignElements->addElement($sName, $mContent, $aAttributes, $sNamespace);

    return $el;
  }

  protected function parseElementForeign(dom\element $el) {

    $mResult = null;
    $parser = $this->loadParser($el->getNamespace());

    if ($parser) {

      $mResult = $parser->parseRoot($el);
    }
    else {

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

      if ($aChildren = $this->parseChildren($el->getChildren())) {

        $newElement->add($aChildren);
      }
      $mResult = $this->parseElementUnknown($el);
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

    foreach ($aForeigns as $sNamespace => $bVal) {

      $parser = $this->loadParser($sNamespace, 'attribute');

      if ($parser) {

        $mResult = $parser->parseAttributes($el, $newElement, $mResult);
      }
      else {

        $this->throwException(sprintf('Unknown attribute with @namespace %s in %s', $sNamespace, $el->asToken()));
      }
    }

    return $mResult;
  }

}
