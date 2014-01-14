<?php

namespace sylma\storage\sql\cached;
use sylma\core;

class Order extends core\module\Managed {

  protected $sPath = '';
  protected $aElements = array();
  protected $content = '';

  public function __construct($sElement) {

    $this->setPath($sElement);
  }

  protected function setPath($sPath) {

    $this->sPath = $sPath;
  }

  protected function getPath() {

    return $this->sPath;
  }

  public function setElements(array $aElements) {

    $this->aElements = $aElements;
    $this->content = $this->build();
  }

  protected function getElement($sName) {

    if (!isset($this->aElements[$sName])) {

      $this->launchException("Unknown order alias : $sName");
    }

    return $this->aElements[$sName];
  }

  public function extractPath() {

    $aElements = explode(',', $this->getPath());
    $aResult = array();

    foreach ($aElements as $sKey => $sElement) {

      $bDirection = true;

      if ($sElement{0} == '!') {

        $sElement = substr($sElement, 1);
        $bDirection = false;
      }

      $aResult[$sKey] = array(
        'name' => $sElement,
        'direction' => $bDirection,
      );
    }

    return $aResult;
  }

  protected function build() {

    foreach ($this->extractPath() as $aPath) {

      $aElement = $this->getElement($aPath['name']);
      $sValue = $aElement['alias'];

      if (isset($aElement['string']) && $aElement['string']) {

        $sValue = 'LOWER(' . $sValue . ')';
      }

      $aResult[] = $sValue . ($aPath['direction'] ? '' : ' DESC');
    }

    return implode(',', $aResult);
  }

  public function __toString() {

    $sContent = $this->content;

    return $sContent ? ' ORDER BY ' . $sContent : '';
  }
}

