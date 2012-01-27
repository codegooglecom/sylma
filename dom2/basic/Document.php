<?php

namespace sylma\dom\basic;
use \sylma\dom, \sylma\storage\fs, \sylma\core;

require_once(dirname(__dir__) . '/document.php');

class Document extends \DOMDocument implements dom\document {

  const NS = 'http://www.sylma.org/dom/basic/Document';

  protected $handler;

  public function __construct($sVersion = '1.0', $sEncoding = 'utf-8') {

    parent::__construct($sVersion, $sEncoding);

    $this->preserveWhiteSpace = false;
  }

  public function __call($sMethod, $aArgs) {

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

  public function importNode(\DOMNode $el, $bDeep = true) {

    $result = null;

    if ((bool) $el->ownerDocument && ($el->ownerDocument !== $this)) {

      $result = parent::importNode($el, $bDeep);
    }
    else {

      $result = $el;
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

  public function getHandler() {

    if (!$this->handler) $this->throwException(t('No handler defined'));

    return $this->handler;
  }

  public function setHandler(dom\handler $handler) {

    $this->handler = $handler;
  }

  public function throwException($sMessage, array $aPath = array()) {

    $handler = $this->getHandler();
    $aPath[] = '@file ' . $this->getPath();

    $handler->throwException($sMessage, $aPath);
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

  public function asToken() {

    return $this->getHandler()->asToken();
  }

  public function asString($bFormat = false) {

    return $this->getHandler()->asString($bFormat);
  }

  public function __toString() {

    return $this->asString();
  }
}
