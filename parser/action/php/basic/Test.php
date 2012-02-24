<?php

namespace sylma\parser\action\php\basic;
use \sylma\core;

require_once('core/argumentable.php');
require_once('Controled.php');

class Test extends Controled implements core\argumentable {

  protected $val1;
  protected $val2;
  protected $op;

  public function __construct(Window $controler, $val1, $val2, $op) {

    $this->setControler($controler);

    $this->val1 = $val1;
    $this->val2 = $val2;
    $this->op = $op;
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'test' => array(
        '@operator' => $this->op,
        'val1' => $this->val1,
        'val2' => $this->val2,
      )));
  }
}