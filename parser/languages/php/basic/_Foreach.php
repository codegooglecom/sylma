<?php

namespace sylma\parser\languages\php\basic;
use sylma\core, sylma\parser\languages\common, sylma\parser\languages\php, sylma\dom;

class _Foreach extends common\basic\Controled implements common\argumentable, common\scope, common\structure {

  protected $aContent = array();
  protected $looped;
  protected $var;

  public function __construct(common\_window $controler, $looped, common\_var $var) {

    $this->setControler($controler);
    $this->looped = $looped;
    $this->var = $var;
  }

  public function addContent($mVal) {

    if (is_array($mVal)) {

      foreach ($mVal as $mSub) $this->addContent($mSub);
    }
    else {

      if (is_object($mVal)) {

        if ($mVal instanceof dom\node) {

          $this->bTemplate = true;
        }
      }

      $this->aContent[] = $this->getControler()->create('line', array($this->getControler(), $mVal));

    }
  }

  public function asArgument() {

    $sArgument = null;

    return $this->getControler()->createArgument(array(
       'foreach' => array(
         'looped' => $this->looped,
         'var' => $this->var,
         'content' => $this->aContent,
       ),
    ));
  }
}