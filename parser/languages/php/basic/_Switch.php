<?php

namespace sylma\parser\languages\php\basic;
use sylma\core, sylma\parser\languages\common, sylma\dom;

class _Switch extends common\basic\Structured implements common\argumentable, common\structure {

  protected $aCases = array();
  protected $test;

  public function __construct(common\_window $controler, $test) {

    $this->setControler($controler);
    $this->setTest($test);
  }

  public function setContent(array $aContent) {

    $this->launchException('Cannot manipulate switch content');
  }

  public function getContent() {

    $this->launchException('Cannot manipulate switch content');
  }

  public function addCase($sName = null, $content = null) {

    if (!$sName) {

      $sName = '';
    }

    if (!isset($this->aCases[$sName])) {

      $this->aCases[$sName] = $this->createCase($sName, $content);
    }
    else {

      $this->aCases[$sName]->addContent($content);
    }
  }

  protected function createCase($sName, $content = null) {

    return $this->getControler()->createCase($sName, $content, $sName && $content);
  }

  public function getTest() {

    return $this->test;
  }

  public function setTest($test) {

    $this->test = $test;
  }

  public function getContents() {

    $aResult = array_map(function($item) {
      return $item->getContent();
    }, $this->aCases);

    return $aResult;
  }

  public function setContents(array $aContents) {

    foreach ($aContents as $sKey => $item) {

      if (isset($this->aCases[$sKey])) {

        $case = $this->aCases[$sKey];
      }
      else {

        $case = $this->createCase($sKey);
        $this->aCases[$sKey] = $case;

        $item = array($item, $this->getWindow()->createBreak());
      }

      $case->setContent($item);
    }
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
       'switch' => array(
         'test' => $this->test,
         array_values($this->aCases),
       )
    ));
  }
}