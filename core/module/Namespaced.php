<?php

namespace sylma\core\module;

abstract class Namespaced {
  
  private $aNamespaces = array();
  private $sNamespace = '';
  private $sPrefix = '';
  
  protected function setNamespace($sUri, $sPrefix = '', $bDefault = true) {
    
    $this->aNamespaces[$sPrefix] = $sUri;
    
    if ($bDefault) {
      
      $this->sNamespace = $sUri;
      $this->sPrefix = $sPrefix;
    }
  }
  
  public function getNamespace($sPrefix = null) {
    
    if ($sPrefix) return array_val($sPrefix, $this->aNamespaces);
    else return $this->sNamespace;
  }
  
  public function getPrefix() {
    
    return $this->sPrefix;
  }
  
  protected function setNamespaces(array $aNS) {
    
    $this->aNamespaces = $aNS;
  }
  
  public function getNS($sPrefix = null) {
    
    if ($sPrefix) return array($sPrefix => array_val($sPrefix, $this->aNamespaces));
    else return $this->aNamespaces;
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
  public function escape() {
    
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
}