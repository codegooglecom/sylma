<?php

class XML_XQuery {
  
  private $mQuery = null;
  private $aNamespaces = array();
  
  public function __construct($mQuery, array $aNamespaces = array()) {
    
    $this->mQuery = $mQuery;
    $this->aNamespaces = $aNamespaces;
  }
  
  private function getQuery() {
    
    $mQuery = $this->mQuery;
    $sResult = '';
    
    if (is_object($mQuery)) {
      
      if ($mQuery instanceof XML_Element) $mQuery = $mQuery->getDocument();
      
      if ($mQuery instanceof XML_Document) {
        
        $oTemplate = new XSL_Document(Controler::getSettings('xquery/@template'));
        $sResult = $oTemplate->parseDocument($mQuery, false);
        
      } else if ($mQuery instanceof XML_CData) {
        
        $sResult = $mQuery->getValue();
      }
      
    } else $sResult = (string) $mQuery;
    
    return $sResult;
  }
  
  public function getNamespaces() {
    
    return $this->aNamespaces;
  }
  
  public function parse() {
    
    return $this->read(true);
  }
  
  public function read($bXML = false) {
    
    $oDB = Controler::getDatabase();
    
    $sQuery = $this->getQuery();
    
    if ($oDB && ($sResult = $oDB->query($sQuery, $this->getNamespaces()))) {
      
      if ($bXML) {
        
        return strtoxml($sResult);
        /*
        $oDocument = new XML_Document('root');
        $oDocument->add()
        if (!$oDocument->isEmpty() && $oDocument->countChildren() > 1) $oResult = $oDocument->getChildren();
        else $oResult = $oDocument->getRoot();*/
        
      } else return $sResult;
    }
    
    return null;
  }
}

