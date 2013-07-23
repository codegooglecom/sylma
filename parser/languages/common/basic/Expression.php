<?php

namespace sylma\parser\languages\common\basic;
use \sylma\core, \sylma\parser\languages\common;

class Expression extends Controled implements common\argumentable {

  protected $aContent = array();

  public function __construct(common\_window $window, array $aValue) {

    $this->setContent($aValue);
    $this->setControler($window);
  }

  protected function setContent(array $aValue) {

    $this->aContent = $aValue;
  }

  public function getContent() {

    return $this->aContent;
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'expression' => $this->getContent(),
    ));
  }
}