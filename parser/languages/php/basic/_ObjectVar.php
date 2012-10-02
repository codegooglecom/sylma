<?php

namespace sylma\parser\languages\php\basic;
use \sylma\parser\languages\common, \sylma\parser\languages\php;

require_once('parser/languages/common/_object.php');

require_once('_Var.php');

class _ObjectVar extends _Var implements common\_object, common\scope {

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

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'var' => array(
        '@name' => $this->getName(),
      ),
    ));
  }
}