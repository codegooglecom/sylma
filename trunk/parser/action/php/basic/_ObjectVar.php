<?php

namespace sylma\parser\action\php\basic;
use \sylma\parser\action\php;

require_once(dirname(__dir__) . '/_object.php');

require_once('_Var.php');

class _ObjectVar extends _Var implements php\_object {

  protected $object;

  public function __construct(php\_window $controler, ObjectInstance $object, $sName) {

    $this->setControler($controler);

    $this->setName($sName);
    $this->setObject($object);
  }

  public function getObject() {

    return $this->object;
  }

  public function setObject(ObjectInstance $object) {

    $this->object = $object;
  }

  public function addContent($mVar) {

    return $this->getControler()->add($mVar);
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'var' => array(
        '@name' => $this->getName(),
      ),
    ));
  }
}