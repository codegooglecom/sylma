<?php

namespace sylma\parser\languages\php\basic;
use \sylma\core, \sylma\parser\languages\common;

\Sylma::load('Controled.php', __DIR__);
\Sylma::load('/core/argumentable.php');

class Instruction extends Controled implements core\argumentable {

  private $content;

  public function __construct(common\_window $controler, $content) {

    $this->setControler($controler);
    //$controler->checkContent($content);
    $this->content = $content;
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'line' => $this->content,
    ));
  }
}