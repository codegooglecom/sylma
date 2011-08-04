<?php

namespace sylma\dom\basic;
use \sylma\dom, \sylma\storage\fs;

require_once('dom2/document.php');

class document extends \DOMDocument implements dom\document {
  
  const NS = 'http://www.sylma.org/dom/basic/document';
  
  /**
   * File linked
   */
  private $file;
  
  public function __construct($sVersion = '1.0', $sEncoding = 'utf-8') {
    
    parent::__construct($sVersion, $sEncoding);
    
  }
  
  // public function isEmpty()
  // public function getFile()
  
  public function setFile(fs\file $file) {
    
    $this->file = $file;
  }
  
  public function loadFile($iMode = Sylma::MODE_READ) {
    
    $bResult = false;
    
    if (!$this->getFile()) {
      
      $this->throwException(t('No file defined'));
    }
    
    if (!$this->getFile()->checkRights($iMode) {
      
      $this->throwException(t('Forbidden access in this mode'));
    }
    
    $bResult = parent::load($file->getRealPath());
    
    return $bResult;
  }
  
  // public function getRoot()
  // public function add()
  // public function set()
  public function createElement($sName, $oContent = '', $aAttributes = null, $sUri = null) {
    
    return new XML_Element($sName, $oContent, $aAttributes, $sUri, $this);
  }
  
  // public static function createFragment($sNamespace = null) {
    
    // $doc = new self;
    
    // $fragment = $doc->createDocumentFragment();
    // $fragment->setNamespace($sNamespace);
    
    // return $fragment;
  // }
  
  // public function validate(XML_Document $oSchema, array $aOptions = array())
  // public function __call($sMethod, $aArguments)
  public function display($bHtml = false, $bDeclaration = true) {
    
    $sResult = '';
    
    if (!$this->isEmpty()) {
      
      if ($bHtml) $sResult = parent::saveXML(null); //LIBXML_NOEMPTYTAG
      else {
        
        if ($bDeclaration) $sResult = parent::saveXML(); // TODO (?) empty tag ar not closed with ../> but with closing tag
        else $sResult = parent::saveHTML(); // entity encoding
      }
    }
    
    if (!$bDeclaration && ($iDec = strpos($sResult, '?>'))) $sResult = substr($sResult, $iDec + 2);
    
    // return $sResult;
  // }
  }
  
  protected function throwException($sMessage, $mSender = array(), $iOffset = 2) {
    
    $mSender = (array) $mSender;
    $mSender[] = '@namespace ' . self::NS;
    
    if ($this->getFile()) $mSender[] = '@file ' . $this->getFile();
    else $sMessage .= ' [unknown file]';
    
    Sylma::throwException($sMessage, $mSender, $iOffset);
  }
  
  public function serialize() {
    
    return $this->display(true, false);
  }
  
  public function unserialize($sDocument) {
    
    return $this->__construct('<?xml version="1.0" encoding="utf-8"?>'."\n".$sDocument);
  }
}
