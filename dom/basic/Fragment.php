<?php

namespace sylma\dom\basic;
use \sylma\dom;

require_once(dirname(__dir__) . '/node.php');
require_once(dirname(__dir__) . '/fragment.php');

class Fragment extends \DOMDocumentFragment implements dom\node, dom\fragment {

  private $sNamespace;
  private $controler;
  const CONTROLER_ALIAS = 'dom';

  public function getControler() {

    return $this->controler;
  }

  public function setControler(dom\Controler $controler) {

    $this->controler = $controler;
  }

  public function setNamespace($sNamespace) {

    $this->sNamespace = $sNamespace;
  }

  public function getNamespace() {

    return $this->sNamespace;
  }

  public function getType() {

    return $this->nodeType;
  }

  public function getParent() {

    return $this->getDocument();
  }

  public function setRoot(dom\element $node) {

    return parent::appendChild($node);
  }

  public function getRoot() {

    return $this->firstChild;
  }

  public function add() {

    $mResult = null;

    if (count(func_get_args()) > 1) {

      $mResult = $this->add(func_get_args());
    }
    else {

      $val = func_get_arg(0);

      if (is_array($val)) {

        $mResult = array();
        foreach ($val as $arg) $mResult[] = $this->add($arg);
      }
      else {

        $mResult = $this->insertChild($val);
      }
    }

    return $mResult;
  }

  protected function insertChild($val) {

    if ($val instanceof dom\collection) {

      $mResult = array();

      foreach ($val as $node) {

        $mResult[] = $this->appendChild($node);
      }
    }
    else {

      $mResult = $this->appendChild($val);
    }

    return $mResult;
  }

  public function getDocument() {

    return $this->ownerDocument;
  }

  public function addNode($sName, $content = null, $aAttributes = array(), $sNamespace = null) {

    $dom = $this->getControler();

    if ($sNamespace === null) $sNamespace = $this->getNamespace();

    $node = $dom->createElement($sName, $content, $aAttributes, $sNamespace);
    $this->appendChild($node);

    return $node;
  }

  public function asToken() {

    return '@fragment in ' . $this->getParent()->asToken();
  }

  public function asString($iMode = 0) {

    $this->getControler()->throwException(t('Cannot convert fragment to string'));
  }

  public function __toString() {

    return $this->asString();
  }
}

