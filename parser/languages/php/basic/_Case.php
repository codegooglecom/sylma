<?php

namespace sylma\parser\languages\php\basic;
use sylma\core, sylma\parser\languages\common;

class _Case extends common\basic\Structured implements common\argumentable, common\scope {

  protected $test;

  public function __construct(common\_window $controler, $test, $content) {

    $this->setControler($controler);
    $this->setTest($test);

    if ($content) $this->addContent($content);
  }

  public function getTest() {

    return $this->test;
  }

  public function setTest($test) {

    $this->test = $this->getControler()->argToInstance($test);
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
       'case' => array(
         'test' => $this->test,
         'content' => $this->getContent(),
       )
    ));
  }
}