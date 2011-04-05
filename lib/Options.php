<?php

require('module/Base.php');

class Options extends ModuleBase {
  
  private $oDocument = null;
  private $aOptions = array(); // cache array
  
  public function __construct(XML_Document $oDocument, XML_Document $oSchema = null, array $aNS = array()) {
    
    $this->oDocument = $oDocument;
    $this->setPrefix($oDocument && $oDocument->getRoot() ? $oDocument->getRoot()->getPrefix() : '');
    
    $this->setNamespaces($aNS);
    if ($oSchema) $this->setSchema($oSchema);
  }
  
  private function getDocument() {
    
    return $this->oDocument;
  }
  
  private function parsePath($sPath) {
    
    if ($sPrefix = $this->getPrefix()) return preg_replace('/([-\w]+)/', $sPrefix.':\1', $sPath);
    else return $sPath;
  }
  
  public function get($sPath, $bDebug = true) {
    
    $eResult = null;
    
    if (!$this->getDocument()) $this->dspm(xt('Aucune option définie'), 'action/warning');
    else {
      
      if (!array_key_exists($sPath, $this->aOptions) || !$this->aOptions[$sPath]) {
        
        $bPrefix = strpos($sPath, ':');
        
        if (!$bPrefix && !strpos($sPath, '/')) { // only first level, can optimize
          
          $eResult = $this->getDocument()->getByName($sPath);
        }
        else { // more than one level, use xpath
          
          if (!$bPrefix) $sRealPath = $this->parsePath($sPath);
          else $sRealPath = $sPath;
          
          $eResult = $this->getDocument()->get($sRealPath, $this->getNS());
        }
        
        $this->aOptions[$sPath] = $eResult;
        
        if (!$this->aOptions[$sPath] && $bDebug) {
          
          dspm(xt('Option %s introuvable dans %s',
            new HTML_Strong($sPath),
            view($this->getDocument())), 'action/warning');
        }
      }
    }
    
    return $eResult;
  }
  
  public function read($sPath, $bDebug = true) {
    
    if ($oOption = $this->get($sPath, $bDebug)) return $oOption->read();
    else return '';
  }
}
