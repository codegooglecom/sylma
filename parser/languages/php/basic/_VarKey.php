<?php

namespace sylma\parser\languages\php\basic;
use \sylma\parser\languages\common, \sylma\parser\languages\php;

class _VarKey extends common\basic\Controled implements common\argumentable {

  protected $var;
  protected $name;

  public function __construct(common\_window $controler, common\_var $var, $name) {

    $this->setControler($controler);

    $this->setName($name);
    $this->setVar($var);
  }

  protected function setName($name) {

    $this->name = $name;
  }

  protected function getName() {

    return $this->name;
  }

  protected function setVar(common\_var $var) {

    if (!$var->getInstance() instanceof instance\_Array) {

      $this->getWindow()->throwException('Can call key only on array');
    }

    $this->var = $var;
  }

  public function getVar($bInsert = true, $sName = '') {

    return $this->var;
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'key' => array(
        'var' => $this->getVar(),
        'name' => $this->getName(),
      ),
    ));
  }
}