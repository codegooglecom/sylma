<?php

namespace sylma\parser\js\binder;
use sylma\core, sylma\dom, sylma\parser;

\Sylma::load('/core/module/Domed.php');
\Sylma::load('/parser/cached/documented.php');

class Cached extends core\module\Domed implements parser\cached\documented {

  protected $parent;

  public function __construct() {

    $this->setDirectory(__FILE__);
    $this->loadDefaultArguments();
  }

  public function getParent() {

    return $this->parent;
  }

  public function setParent($parent) {

    $this->parent = $parent;
  }

  public function parseDocument(dom\handler $doc) {

    $root = $this->getTemplate('cached.xsl')->parseDocument($doc);

    $obj = $this->getControler('parser')->create('js/binder/object', array($root));

    return $doc;
  }

}
