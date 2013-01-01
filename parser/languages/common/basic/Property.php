<?php

namespace sylma\parser\languages\common\basic;
use sylma\core, sylma\parser\languages\common;

class Property extends Returned implements common\argumentable, common\property {

  protected $parent;
  protected $sName;
  protected $return;

  public function __construct(common\_window $window, common\argumentable $parent, $sName, common\ghost $return = null) {

    $this->setControler($window);
    $this->setParent($parent);
    $this->setName($sName);

    if ($return) $this->setReturn($return);
  }

  protected function getParent() {

    return $this->parent;
  }

  protected function setParent($parent) {

    $this->parent = $parent;
  }

  protected function getName() {

    return $this->sName;
  }

  protected function setName($sName) {

    $this->sName = $sName;
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'property' => array(
        '@name' => $this->getName(),
        $this->getParent(),
      )
    ));
  }
}

