<?php

namespace sylma\parser\languages\php\basic;
use sylma\core, sylma\parser\languages\common, sylma\parser\languages\php, sylma\dom;

class _Foreach extends common\basic\Structured implements common\argumentable, common\scope, common\structure, common\addable {

  protected $looped;
  protected $var;

  public function __construct(common\_window $controler, $looped, common\_var $var) {

    $this->setControler($controler);
    $this->looped = $looped;
    $this->var = $var;
    $var->insert(null, false);
  }

  public function onAdd() {

    $this->getControler()->loadContent($this->looped);
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
       'foreach' => array(
         'looped' => $this->looped,
         'var' => $this->var,
         'content' => $this->getContent(),
       ),
    ));
  }
}