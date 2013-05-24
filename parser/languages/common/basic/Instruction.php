<?php

namespace sylma\parser\languages\common\basic;
use \sylma\core, \sylma\parser\languages\common;

class Instruction extends Controled implements core\argumentable, common\instruction {

  private $content;

  public function __construct(common\_window $controler, $content) {

    $this->setControler($controler);
    //$controler->checkContent($content);
    $this->content = $content;
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'instruction' => $this->content,
    ));
  }
}