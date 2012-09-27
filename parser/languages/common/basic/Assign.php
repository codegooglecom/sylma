<?php

namespace sylma\parser\languages\common\basic;
use sylma\core, sylma\parser\languages\common;

\Sylma::load('/parser/languages/common/basic/Controled.php');
\Sylma::load('/core/argumentable.php');

class Assign extends Controled implements core\argumentable {

  protected $variable;
  protected $value;

  public function __construct(common\_window $controler, common\_var $variable, $value) {

    $this->variable = $variable;
    $this->value = $value;
    $this->setControler($controler);
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'assign' => array(
        'variable' => $this->variable,
        'value' => $this->value,
      )));
  }
}