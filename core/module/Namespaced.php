<?php

namespace sylma\core\module;

abstract class Namespaced extends Exceptionable {

  protected $aNamespaces = array();
  private $sNamespace = '';
  private $sPrefix = '';

  protected function setNamespace($sNamespace, $sPrefix = null, $bDefault = true) {

    if (!$sNamespace) {

      $this->throwException('Cannot use empty string as dom namespace');
    }

    if ($sPrefix) $this->aNamespaces[$sPrefix] = $sNamespace;

    if ($bDefault) {

      $this->sNamespace = $sNamespace;
      $this->sPrefix = $sPrefix;
    }
  }

  protected function getNamespace($sPrefix = null) {

    if ($sPrefix) {
/*
      if (!array_key_exists($sPrefix, $this->aNamespaces)) {

        $this->throwException(sprintf('Unknown prefix : %s', $sPrefix));
      }
*/
      $sResult = array_key_exists($sPrefix, $this->aNamespaces) ? $this->aNamespaces[$sPrefix] : null;
    }
    else {

      $sResult = $this->sNamespace;
    }

    return $sResult;
  }

  protected function getPrefix() {

    return $this->sPrefix;
  }

  protected function setNamespaces(array $aNS) {

    $this->aNamespaces = $this->mergeNamespaces($aNS);
  }

  protected function getNS($sPrefix = null) {

    if ($sPrefix) $aResult = array($sPrefix => $this->getNamespace ($sPrefix));
    else $aResult = $this->aNamespaces;

    return $aResult;
  }

  protected function mergeNamespaces(array $aNamespaces = array()) {

    $aResult = $this->getNS();

    foreach ($aNamespaces as $sPrefix => $sNamespace) {

      if ($sPrefix) $aResult[$sPrefix] = $sNamespace;
    }

    return $aResult;
  }
}