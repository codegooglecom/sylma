<?php

namespace sylma\dom\argument;
use sylma\core, sylma\dom;

require_once('core/module/Controled.php');
require_once('core/argument.php');

class Basic extends core\module\Controled implements core\argument {
  
  const NS = 'http://www.sylma.org/dom/argument';
  const PREFIX_DEFAULT = 'self';
  
  private $document = null;
  private $schema = null;
  
  private $aOptions = array(); // cache array
  
  public function __construct(dom\document $doc, array $aNS = array()) {
    
    $this->setNamespaces($aNS);
    
    // first element define default namespace & prefix
    
    if (!$this->getPrefix()) {
      
      if ($sNamespace = $this->getNamespace()) {

        $this->setNamespace($sNamespace, self::PREFIX_DEFAULT);
      }
      else {

        $root = $doc->getRoot();

        $this->setNamespace($root->getNamespace(), self::PREFIX_DEFAULT);
      }
    }
    
    $this->setDocument($doc);
  }
  
  public function setParent(core\argument $parent) {
    
    $this->parent = $parent;
  }
  
  public function getParent() {
    
    return $this->parent;
  }
  
  public function setDocument(dom\handler $document) {
    
    if ($document->isEmpty()) {
      
      $this->throwException(t('Cannot use empty doc as option\'s content'));
    }
    
    $document->registerNamespaces($this->getNS());
    $this->document = $document;
  }
  
  public function getDocument() {
    
    return $this->document;
  }
  
  protected function parsePath($sPath) {

    $sResult = $sPath;
    
    $aPath = explode('/', $sPath);
    
    if (!$aPath) $aPath = array($sPath);
    
    foreach ($aPath as &$sSub) {
      
      if (strpos($sSub, ':') === false) {
        
        $sSub = $this->getPrefix() . ':' . $sSub;
      }
    }
    
    return implode('/', $aPath);
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
    
    $result = null;
    $dom = $this->getControler('dom');
    
    $sRealPath = $this->parsePath($sPath);
    
    $result = $this->getDocument()->getx($sRealPath, array(), $bDebug);

    if (!$result instanceof dom\element || !$result->isComplex()) {

      $this->throwException(txt('Cannot use @path %s as complex element', $sPath));
    }

    $result = new self($dom->create('handler', array($result)), null, $this->getNS());
    
    return $result;
  }
  
  public function read($sPath = '', $bDebug = true) {
    
    $sPath = $this->parsePath($sPath);

    $sResult = $this->getDocument()->readx($sPath, array(), $bDebug);
    
    return $sResult;
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
  
  public function normalize() {
    
    
  }
  
  public function asArray() {
    
    
  }
  
  protected function throwException($sMessage, $mSender = array(), $iOffset = 2) {
    
    $mSender[] = $this->getNamespace();
    parent::throwException($sMessage, $mSender, $iOffset);
  }
}
