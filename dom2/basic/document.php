<?php

namespace sylma\dom\basic;
use \sylma\dom, \sylma\storage\fs;

require_once('dom2/document.php');

class Document extends \DOMDocument implements dom\document {
  
  const NS = 'http://www.sylma.org/dom/basic/document';
  
  /**
   * See @method setControler()
   * @var dom\Controler
   */
  private $controler;
  
  /**
   * See @method setFile()
   * @var fs\file
   */
  private $file;
  
  public function __construct($sVersion = '1.0', $sEncoding = 'utf-8') {
    
    parent::__construct($sVersion, $sEncoding);
  }
  
  // public function isEmpty()
  // public function getFile()
  public function __call($sMethod, $aArgs) {
    
    $mResult = null;
    
    if ($root = $this->getRoot()) {
      
      $method = new \ReflectionMethod($this->getRoot(), $sMethod);
      $mResult = $method->invokeArgs($root, $aArgs);
    }
    
    return $mResult;
  }
  
  public function getControler() {
    
    return $this->controler;
  }
  
  public function setControler(dom\Controler $controler) {
    
    $this->controler = $controler;
  }
  
  public function registerClasses(core\argument $settings = null) {
    
    if (!$this->getControler()) {
      
      $this->throwException(t('Cannot register classes, no controler has been defined'));
    }
    
    $aClasses = $this->getControler()->getClasses($settings);
    
    foreach ($aClasses as $sOrigin => $sReplacement) {
      
      $this->registerNodeClass($sOrigin, $sReplacement);
    }
  }
  
  public function setFile(fs\file $file) {
    
    $this->file = $file;
  }
  
  public function getFile() {
    
    return $this->file;
  }
  
  public function loadFile() {
    
    $bResult = false;
    
    if (!$this->getFile()) {
      
      $this->throwException(t('No file defined'));
    }
    
    return $this->loadContent();;
  }
  
  protected function loadContent() {
    
    $sContent = $this->getFile()->read();
    
    $reader = new \XMLReader;
    $reader->XML($sContent);
    
    $aNamespaces = $this->lookupNamespaces($reader);
    
    return parent::loadXML($this->getFile()->read());
  }
  
  private function lookupNamespaces(\XMLReader $reader) {
    
    $aNamespaces = array();
    
    while ($reader->read()) {
      
      switch ($reader->nodeType) {
        
        // case \XMLReader::NONE : break;
        case \XMLReader::ELEMENT :
          
          $aNamespaces[$reader->namespaceURI] = true;
          
          if($reader->hasAttributes) {
            
            while($reader->moveToNextAttribute()) {
              
              $aNamespaces[$reader->namespaceURI] = true;
            }
          }
          
          if (!$reader->isEmptyElement) {
            
            $aNamespaces = array_merge($aNamespaces, $this->lookupNamespaces($reader));
          }
          
        break;
        // case \XMLReader::ATTRIBUTE : break;
        // case \XMLReader::TEXT : break;
        case \XMLReader::END_ELEMENT : //dspf($reader->expand(new \XML_Element)); break 2;
        // case \XMLReader::XML_DECLARATION : break;
      }
    }
    
    return $aNamespaces;
  }
  
  public function registerNamespaces(array $aNamespaces = array()) {
    
    
  }
  
  public function getRoot() {
    
    if (isset($this->documentElement)) return $this->documentElement;
    else return null;
  }
  
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
