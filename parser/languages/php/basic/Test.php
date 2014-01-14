<?php

namespace sylma\parser\languages\php\basic;
use \sylma\core, sylma\parser\languages\common;

class Test extends common\basic\Controled implements core\argumentable {

  protected $val1;
  protected $val2;
  protected $op;

  public function __construct(common\_window $window, $val1, $val2, $op) {

    $this->setWindow($window);

    $this->val1 = $window->argToInstance($val1);
    $this->val2 = $window->argToInstance($val2);
    $this->op = $op;
  }

  public function asArgument() {

    return $this->getWindow()->createArgument(array(
      'test' => array(
        '@operator' => $this->op,
        'val1' => $this->val1,
        'val2' => $this->val2,
      )));
  }
}