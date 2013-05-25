<?php

namespace sylma\dom\basic;
use \sylma\dom, \sylma\storage\fs, \sylma\core;

class Document extends \DOMDocument implements dom\document {

  const NS = 'http://www.sylma.org/dom/basic/Document';

  protected $handler;

  public function __construct($sVersion = '1.0', $sEncoding = 'utf-8') {

    parent::__construct($sVersion, $sEncoding);

    $this->preserveWhiteSpace = false;
  }

  public function __call($sMethod, $aArgs) {

    $this->throwException('Auto call not allowed with ' . $sMethod);

    $mResult = null;

    if ($root = $this->getRoot()) {

      $method = new \ReflectionMethod($this->getRoot(), $sMethod);
      $mResult = $method->invokeArgs($root, $aArgs);
    }

    return $mResult;
  }
  
  /**
   * Allow implementation of node interface
   * @return dom\document
   */
  public function getDocument() {

    return $this;
  }

  public function getType() {

    return $this->nodeType;
  }

  public function getParent() {

    return null;
  }

  public function importNode(\DOMNode $node, $bDeep = true) {

    $result = null;

    if ((bool) $node->ownerDocument && ($node->ownerDocument !== $this)) {

      $result = parent::importNode($node, $bDeep);
    }
    else {

      $result = $node;
    }

    // Import error can append with not-prefixed element

    if (\Sylma::read('debug/xml/import')) {

      if ($node instanceof dom\element) {

        if ($node->compare($result)) {

          $this->throwException('Bad import compare fail  on : ' . $node->compareBadNode->asToken());
        }
      }
    }

    return $result;
  }

  public function setRoot(dom\element $el) {

    $result = null;

    if (!$this->isEmpty()) $this->getRoot()->remove();

    $el = $this->importNode($el);
    $result = $this->appendChild($el);

    return $result;
  }

  public function getRoot() {

    if (isset($this->documentElement)) return $this->documentElement;
    else return null;
  }

  public function isEmpty() {

    return !$this->getRoot();
  }

  public function getHandler($bDebug = true) {

    if ($bDebug && !$this->handler) {

      $this->throwException('No handler defined');
    }

    return $this->handler;
  }

  public function setHandler(dom\handler $handler) {

    $this->handler = $handler;
  }

  public function throwException($sMessage, array $aPath = array()) {

    if ($handler = $this->getHandler(false)) {

      $handler->throwException($sMessage, $aPath);
    }
    else {

      \Sylma::throwException($sMessage, $aPath);
    }
  }

  public function display($bHtml = false, $bDeclaration = true) {

    $sResult = '';

    if (!$this->isEmpty()) {

      if ($bHtml) $sResult = parent::saveXML(null); //LIBXML_NOEMPTYTAG
      else {

        if ($bDeclaration) $sResult = parent::saveXML(); // TODO (?) empty tag ar not closed with ../> but with closing tag
        else $sResult = parent::saveHTML(); // entity encoding
      }
    }

    if (!$bDeclaration && ($iDec = strpos($sResult, '?>'))) $sResult = substr($sResult, $iDec + 2);

    // return $sResult;
  // }
  }

  public function serialize() {

    return $this->display(true, false);
  }

  public function unserialize($sDocument) {

    return $this->__construct('<?xml version="1.0" encoding="utf-8"?>'."\n".$sDocument);
  }

  public function remove() {

    return null;
  }

  public function asToken() {

    return $this->getHandler()->asToken();
  }

  public function asString($iMode = 0) {

    return $this->getHandler()->asString($iMode);
  }

  public function __toString() {

    return $this->asString();
  }
}
