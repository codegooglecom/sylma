<?php

namespace sylma\parser\languages\php\basic;
use \sylma\core, \sylma\parser\languages\common, \sylma\parser\languages\php;

\Sylma::load('Controled.php', __DIR__);
\Sylma::load('/core/argumentable.php');

class _Closure extends common\basic\Controled implements common\scope, core\argumentable {

  protected $aArguments = array();
  protected $return;

  public function __construct(common\_window $controler, array $aArguments = array()) {

    $this->setControler($controler);
    $this->aArguments = $aArguments;
  }

  public function addContent($mVar) {

    if (is_array($mVar)) {

      foreach ($mVar as $mSub) {

        $this->addContent($mSub);
      }
    }
    else {

      $this->aContent[] = $this->getControler()->create('line', array($this->getControler(), $mVar));
      $this->setReturn($mVar);
    }

    return $this->getReturn();
  }

  protected function setReturn($mVar) {

    $this->return = $mVar;
  }

  protected function getContent() {

    return $this->aContent;
  }

  protected function getArguments() {

    return $this->aArguments;
  }

  public function getReturn() {

    return $this->return;
  }

  public function asArgument() {

    $aContent = $this->getContent();
    $return = array_pop($aContent);

    return $this->getControler()->createArgument(array(
       'closure' => array(
         '#argument' => $this->getArguments(),
         $aContent,
         'return' => array($return),
       ),
    ));
  }
}