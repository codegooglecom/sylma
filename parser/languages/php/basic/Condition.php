<?php

namespace sylma\parser\languages\php\basic;
use sylma\core, sylma\parser\languages\common, sylma\parser\languages\php, sylma\dom;

class Condition extends common\basic\Structured implements common\argumentable, common\scope, common\structure {

  protected $aElse = array();
  protected $test;

  public function __construct(common\_window $controler, $test, $content = null) {

    $this->setControler($controler);
    $this->setTest($test);

    if ($content) $this->addContent($content);
  }

  public function _addContent($mVal) {

    if (is_array($mVal)) {

      foreach ($mVal as $mSub) $this->addContent($mSub);
    }
    else {

      if (is_object($mVal)) {

        if ($mVal instanceof dom\node) {

          $this->bTemplate = true;
        }
      }

      $this->aContent[] = $this->createLine($mVal);
    }
  }

  public function addElse($mVal) {

    $this->aElse[] = $this->createLine($mVal);
  }

  public function getTest() {

    return $this->test;
  }

  public function setTest($test) {

    $this->test = $test;
  }

  public function asArgument() {

    $sArgument = null;
    $window = $this->getControler();

    if ($this->useTemplate()) {

      $insert = $window->createInsert($window->argToInstance(true), '', null, false);
      $sArgument = $insert->getKey();
      $this->addContent($insert);
      $window->add($window->createInsert($window->argToInstance(false), '', $insert->getKey(), false));
    }

    return $this->getControler()->createArgument(array(
       'condition' => array(
         //'@context' => $window->getContext(),
         '@argument' => $sArgument,
         'test' => $this->test,
         'content' => $this->getContent(),
         'else' => $this->aElse,
       )
    ));
  }
}