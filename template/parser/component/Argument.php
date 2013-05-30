<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\template\parser;

class Argument extends Variable implements parser\component, common\arrayable {

  protected $sName;
  protected $bFilled = false;

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->loadName();
  }

  public function setContent($mContent) {

    $this->bFilled = true;
    $this->loadVar($mContent);
  }

  public function asArray() {

    return $this->bFilled ? array($this->getVar()->getInsert()) : array();
    //return array();
  }
}

