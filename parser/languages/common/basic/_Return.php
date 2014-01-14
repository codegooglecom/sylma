<?php

namespace sylma\parser\languages\common\basic;
use sylma\core, sylma\parser\languages\common;

class _Return extends Controled implements common\argumentable {

  public function __construct(common\_window $window, $content) {

    $this->setWindow($window);
    $this->content = $content;
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'return' => $this->content,
    ));
  }
}

