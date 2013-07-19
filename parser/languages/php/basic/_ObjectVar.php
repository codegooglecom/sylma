<?php

namespace sylma\parser\languages\php\basic;
use \sylma\parser\languages\common, \sylma\parser\languages\php;

class _ObjectVar extends _Var implements common\_object, common\scope, common\_callable {

  protected $object;

  public function __construct(common\_window $controler, common\_object $object, $sName, common\argumentable $content = null) {

    $this->setControler($controler);

    $this->setName($sName);
    $this->setInstance($object);
    if ($content) $this->setContent($content);
  }

  public function getInterface() {

    return $this->getInstance()->getInterface();
  }

  public function addContent($mVar) {

    return $this->getControler()->add($mVar);
  }

  public function call($sMethod, array $aArguments = array(), $mReturn = null, $bVar = false) {

    $call = $this->getControler()->createCall($this, $sMethod, $mReturn, $aArguments);

    return $bVar ? $call->getVar() : $call;
  }

  public function asArgument() {

    //$this->checkInserted();

    return $this->getControler()->createArgument(array(
      'var' => array(
        '@name' => $this->getName(),
      ),
    ));
  }
}