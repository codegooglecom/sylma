<?php

namespace sylma\parser\languages\js\basic\instance;
use \sylma\core, \sylma\parser\languages\common, \sylma\parser\languages\js;

class _Function extends _Object implements common\scope {

  protected $return;
  protected $aArguments;

  protected $aContent;

  public function __construct(common\_window $window, array $aArguments = array(), $sContent = '', common\ghost $return = null) {

    $this->setControler($window);
    $this->setInterface('Function');

    $this->setArguments($aArguments);

    if ($return) $this->setReturn($return);
    if ($sContent) $this->setTextContent ($sContent);
  }

  public function addContent($mVar) {

    if (is_array($mVar)) {

      foreach ($mVar as $mSub) {

        $this->addContent($mSub);
      }
    }
    else {

      $this->aContent[] = $this->getControler()->createInstruction($mVar);
    }

    return end($this->aContent);
  }

  public function setTextContent($sContent) {

    $this->aContent = array($this->getControler()->createCode($sContent));
  }

  protected function setArguments($aArguments) {

    $this->aArguments = $aArguments;
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

  public function setReturn(common\ghost $instance) {

    $this->return = $instance;
  }

  public function asArgument() {

    $aContent = $this->getContent();
    //$return = array_pop($aContent);

    return $this->getControler()->createArgument(array(
       'function' => array(
         'arguments' => array(
           '#argument' => $this->getArguments()
         ),
         'content' => $aContent,
       ),
    ));
  }
}