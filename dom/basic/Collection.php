<?php

namespace sylma\dom\basic;
use \sylma\dom, \sylma\core;

require_once(dirname(__dir__) . '/collection.php');

class Collection implements \Iterator, dom\collection {

  private $aNodes = array();
  private $aStore = array();
  public $current = 0;

  public $length = 0;

  public function __construct($mValues = null) {

    if ($mValues) $this->addArray($mValues);
  }

  public function item($iKey) {

    if (array_key_exists($iKey, $this->aNodes)) return $this->aNodes[$iKey];
    else return null;
  }

  public function rewind() {

    $this->current = 0;
  }

  public function next() {

    $this->current++;
  }

  public function key() {

    return $this->current;
  }

  public function current() {

    if (array_key_exists($this->current, $this->aNodes)) return $this->aNodes[$this->current];
    else return null;
  }

  public function getFirst() {

    return $this->item(0);
  }

  public function getLast() {

    return $this->item($this->length - 1);
  }

  public function valid() {

    return ($this->current < count($this->aNodes));
  }

  public function add($content) {

    if ($content instanceof \DOMNodeList) $this->addList($content);
    else if ($content instanceof dom\collection) $this->addCollection($content);
    else $this->addNode($content);
  }

  public function addList(\DOMNodeList $list) {

    return $this->addArray($list);
  }

  public function addCollection(dom\collection $collection) {

    return $this->addArray($collection);
  }

  public function addNode(dom\node $mValue) {

    $this->aNodes[] = $mValue;
    $this->length++;
  }

  protected function addArray($list) {

    foreach ($list as $node) $this->addNode($node);
  }

  public function setIndex($current) {

    $this->current = $current;
  }

  public function store() {

    $this->aStore[] = $this->current;
  }

  public function restore() {

    $this->current = array_pop($this->aStore);
  }

  public function reverse() {

    $this->aNodes = array_reverse($this->aNodes);
    $this->rewind();
  }

  public function implode($sSeparator = ' ') {

    $aResult = array();

    foreach ($this->aNodes as $oNode) {

      $aResult[] = $oNode;
      $aResult[] = $sSeparator;
    }

    array_pop($aResult);
    return $aResult;
  }

  public function __toString() {

    return implode('', $this->implode());
  }
}

