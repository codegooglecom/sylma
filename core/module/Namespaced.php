<?php

namespace sylma\core\module;

abstract class Namespaced extends Exceptionable {

  private $aNamespaces = array();
  private $sNamespace = '';
  private $sPrefix = '';

  protected function setNamespace($sNamespace, $sPrefix = null, $bDefault = true) {

    if (!$sNamespace) {

      $this->throwException('Cannot use empty string as dom namespace');
    }

    $this->aNamespaces[$sPrefix] = $sNamespace;

    if ($bDefault && (!$this->sNamespace || !$sPrefix)) {

      $this->sNamespace = $sNamespace;
      $this->sPrefix = $sPrefix;
    }
  }

  protected function getNamespace($sPrefix = null) {

    if ($sPrefix) {

      if (!array_key_exists($sPrefix, $this->aNamespaces)) {

        $this->throwException(sprintf('Unknown prefix : %s', $sPrefix));
      }

      $sResult = $this->aNamespaces[$sPrefix];
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

    foreach($aNS as $sPrefix => $sNamespace) {

      // prefix 0 identify main namespace

      if (!$sPrefix) $this->setNamespace($sNamespace);
      else $this->setNamespace($sNamespace, $sPrefix, false);
    }
  }

  protected function getNS($sPrefix = null) {

    if ($sPrefix) $aResult = array($sPrefix => $this->getNamespace ($sPrefix));
    else $aResult = $this->aNamespaces;

    return $aResult;
  }

  /**
   * Escape a string for secured queries to module's related storage system
   * <code>
   * list($spUser, $spPassword) = $this->escape($sUser, sha1($sPassword));
   * </code>
   *
   * @param string A single or a list of string values to escape
   * @return string|array An escaped string or array of strings
   */
  protected function escape() {

    $mResult = null;

    if (func_num_args() != 1) {

      $mResult = array();

      foreach (func_get_args() as $mValue) $mResult[] = $this->escapeString($mValue);
    }
    else if ($sValue = (string) func_get_arg(0)) {

      $mResult = $this->escapeString($sValue);
    }

    return $mResult;
  }

  private function escapeString($sValue) {

    return "'".addslashes($sValue)."'";
  }

  protected function mergeNamespaces(array $aNamespaces = array()) {

    if ($aNamespaces) return array_merge($this->getNS(), $aNamespaces);
    else return $this->getNS();
  }

  protected function throwException($sMessage, $mSender = array(), $iOffset = 2) {

    $mSender = (array) $mSender;
    $mSender[] = '@class ' . get_class($this);

    \Sylma::throwException($sMessage, $mSender, $iOffset);
  }
}