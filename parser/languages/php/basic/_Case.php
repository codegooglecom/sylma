<?php

namespace sylma\parser\languages\php\basic;
use sylma\core, sylma\parser\languages\common;

class _Case extends common\basic\Structured implements common\argumentable, common\scope {

  protected $test;

  public function __construct(common\_window $controler, $test, $content, $bBreak = true) {

    $this->setControler($controler);
    $this->setTest($test);

    if ($content) $this->addContent($content);
    if ($bBreak) $this->aContent[] = $this->getWindow()->createBreak();
  }

  public function getTest() {

    return $this->test;
  }

  public function setTest($test) {

    $this->test = $test ? $this->getControler()->argToInstance($test) : $test;
  }

  public function asArgument() {

    $test = $this->getTest();

    if ($test === '') {

      $aResult = array(
       'default' => array(
         'content' => $this->getContent(),
       ),
      );
    }
    else {

      $aResult = array(
       'case' => array(
         'test' => $test,
         'content' => $this->getContent(),
       ),
      );

    }

    return $this->getControler()->createArgument($aResult);
  }
}