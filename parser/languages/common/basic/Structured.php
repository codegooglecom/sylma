<?php

namespace sylma\parser\languages\common\basic;
use sylma\dom;

class Structured extends Controled {

  protected $aContent = array();
  protected $bTemplate = false;

  public function addContent($mVal) {

    $this->addToContent($this->aContent, $mVal);
  }

  protected function addToContent(&$aContent, $mVal) {

    if (is_array($mVal)) {

      foreach ($mVal as $mSub) $this->addToContent($aContent, $mSub);
    }
    else {

      $aContent[] = $this->transformContent($mVal);
    }
  }

  protected function getContent() {

    return $this->aContent;
  }

  protected function transformContent($mVal) {

    if (is_object($mVal)) {

      if ($mVal instanceof dom\node) {

        $this->bTemplate = true;
      }
    }

    $mResult = $this->createLine($mVal);

    return $mResult;
  }

  protected function createLine($mVal) {

    return $this->getControler()->create('line', array($this->getControler(), $mVal));
  }

  protected function useTemplate() {

    return $this->bTemplate;
  }
}