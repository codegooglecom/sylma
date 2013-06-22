<?php

namespace sylma\parser\languages\php\basic;
use sylma\core, sylma\parser\languages\common, sylma\parser\languages\php, sylma\dom;

class Condition extends common\basic\Structured implements common\argumentable, common\scope, common\structure {

  protected $aElse = array();
  protected $test;

  public function __construct(common\_window $controler, $test = null, $content = null) {

    $this->setControler($controler);
    if ($test) $this->setTest($test);

    if ($content) $this->addContent($content);
  }

  public function addElse($mVal) {

    $this->aElse[] = $this->addToContent($this->aElse, $mVal);
  }

  public function setElse(array $aContent) {

    $this->aElse = $aContent;
  }

  protected function getElse() {

    return $this->aElse;
  }

  public function getTest() {

    return $this->test;
  }

  public function setTest($test) {

    $this->test = $test;
  }

  public function _onAdd() {

    $window = $this->getWindow();

    $window->loadContent($this->getTest());
    $window->loadContent($this->getContent());
    $window->loadContent($this->getElse());
  }

  public function getContents() {

    return array(
      'main' => $this->getContent(),
      'else' => $this->getElse(),
    );
  }

  public function setContents(array $aContents) {

    parent::setContents($aContents);
    $this->setElse($aContents['else']);
  }

  protected function parseTest() {

    $test = $this->test;
    $aResult = $aBefore = array();

    $aContent = $this->getWindow()->parseArrayables(array($test));

    foreach ($aContent as $sub) {

      if ($sub instanceof common\instruction) {

        $aBefore[] = $sub;
      }
      else if (is_string($sub)) {

        $aResult[] = $this->getWindow()->createString($sub);
      }
      else {

        $aResult[] = $sub;
      }
    }

    return array($aBefore, $aResult);
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

    list($aBefore, $test) = $this->parseTest();
    
    return  $this->getWindow()->createArgument(array(
      $aBefore,
      'condition' => array(
        //'@context' => $window->getContext(),
        '@argument' => $sArgument,
        'test' => $test,
        'content' => $this->getContent(),
        'else' => $this->aElse ? $this->aElse : null,
      ),
    ));
  }
}