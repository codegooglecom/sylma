<?php

namespace sylma\parser\action\php\basic;
use \sylma\parser\action\php;

require_once(dirname(__dir__) . '/_object.php');

require_once('_Var.php');

class _ObjectVar extends _Var implements php\_object {

  protected $object;

  public function __construct(php\_window $controler, php\basic\instance\_Object $object, $sName) {

    $this->setControler($controler);

    $this->setName($sName);
    $this->setInstance($object);
  }

  public function addContent($mVar) {

    return $this->getControler()->addContent($mVar);
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'var' => array(
        '@name' => $this->getName(),
      ),
    ));
  }
}