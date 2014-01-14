<?php

namespace sylma\parser\languages\common\basic;
use sylma\dom;

class Structured extends Controled {

  protected $aContent = array();
  protected $bTemplate = false;

  protected $bExtracted = false;

  public function addContent($mVal) {

    $this->addToContent($this->aContent, $mVal);
  }

  protected function addToContent(&$aContent, $mVal) {

    if (is_array($mVal)) {

      foreach ($mVal as $mSub) $this->addToContent($aContent, $mSub);
    }
    else {

      if ($mVal instanceof dom\node) {

        $this->bTemplate = true;
      }

      $aContent[] = $this->getWindow()->transformContent($mVal);
    }
  }

  public function setContent(array $aContent) {

    $this->aContent = $aContent;
  }

  public function getContent() {

    return $this->aContent;
  }

  public function getContents() {

    return array(
      'main' => $this->getContent(),
    );
  }

  public function setContents(array $aContents) {

    $this->setContent($aContents['main']);
  }

  public function isExtracted($bVal = null) {

    if (is_bool($bVal)) $this->bExtracted = $bVal;

    return $this->bExtracted;
  }

  protected function useTemplate() {

    return $this->bTemplate;
  }
}