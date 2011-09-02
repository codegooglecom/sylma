<?php

namespace sylma\dom;
use \sylma\core;

class Argument extends core\Namespaced implements core\argument {
  
  private $document = null;
  private $schema = null;
  
  private $aOptions = array(); // cache array
  
  public function __construct(dom\document $doc, dom\document $schema = null, array $aNS = array()) {
    
    $this->document = $doc;
    
    // first element define default prefix
    $this->setPrefix($doc && $doc->getRoot() ? $doc->getRoot()->getPrefix() : '');
    
    $this->setNamespaces($aNS);
    $this->schema = $schema;
  }
  
  public function setParent(SettingsInterface $parent) {
    
    $this->parent = $parent;
  }
  
  public function getParent() {
    
    return $this->parent;
  }
  
  public function getDocument() {
    
    return $this->document;
  }
  
  public function setPrefix($sPrefix) {
    
    $this->sPrefix = $sPrefix;
  }
  
  public function getPrefix() {
    
    return $this->sPrefix;
  }
  
  protected function parsePath($sPath) {
    
    if ($sPrefix = $this->getPrefix()) return preg_replace('/([-\w]+)/', $sPrefix.':\1', $sPath);
    else return $sPath;
  }
  
  public function validate() {
    
    $bResult = false;
    
    if (!$this->getSchema()) {
      
      $this->dspm(xt('Cannot validate, no schema defined'), 'warning');
    }
    else if (!$this->getDocument() || $this->getDocument()->isEmpty()) {
      
      $this->dspm(xt('Cannot validate, document empty or not defined'), 'warning');
    }
    else {
      
      $bResult = $this->getDocument()->validate($this->schema);
    }
    
    return $bResult;
  }
  
  public function get($sPath = '', $bDebug = true) {
    
    $nResult = null;
    
    if (!$this->getDocument()) {
      
      $this->dspm(xt('Cannot load value %s, no document defined',
        new HTML_Strong($sPath)), 'error');
    }
    else {
      
      if (!array_key_exists($sPath, $this->aOptions) || !$this->aOptions[$sPath]) {
        
        $bPrefix = (strpos($sPath, ':') !== false);
        
        if (!$bPrefix && strpos($sPath, '/') === false) { // only first level, can optimize
          
          $nResult = $this->getDocument()->getByName($sPath);
        }
        else { // more than one level, use xpath
          
          if (!$bPrefix) $sRealPath = $this->parsePath($sPath);
          else $sRealPath = $sPath;
          
          $nResult = $this->getDocument()->get($sRealPath, $this->getNS());
        }
        
        $this->aOptions[$sPath] = $nResult;
        
        if (!$nResult && $bDebug) {
          
          dspm(xt('Option %s not found in %s',
            new HTML_Strong($sPath),
            view($this->getDocument())), 'action/warning');
        }
        else if (is_object($nResult)) {
          
          // Transform node result in options
          
          $nResult = new self(new XML_Document($nResult), null, $this->getNS());
        }
      }
    }
    return $nResult;
  }
  
  public function read($sPath = '', $bDebug = true) {
    
    if ($oOption = $this->get($sPath, $bDebug)) return $oOption->read();
    else return '';
  }
  
  // public function add($mValue = null) {
  
  public function set($sPath = '', $mValue = null) {
    
    $mResult = '';
    
    if ($eOption = $this->get($sPath)) {
      
      if ($mValue) $mResult = $eOption->set($mValue);
      else $mResult = $eOption->remove();
    }
    
    return $mResult;
  }
}
