<?php

namespace sylma\parser\xslt;
use \sylma\dom, \sylma\parser;

require_once('dom2/basic/Handler.php');

class Handler extends dom\basic\Handler {
  
  const NS = 'http://www.w3.org/1999/XSL/Transform';
  const IMPORT_LEVEL_MAX = 10;
  
  private $processor = null;
  
  public function __construct($mChildren = '', $iMode = \Sylma::MODE_READ, array $aNamespaces = array()) {
    
    $this->setProcessor(new \XSLTProcessor);
    
    parent::__construct($mChildren, $iMode);
  }
  
  public function removeParameter($sName, $sUri = '') {
    
    $bResult = $this->getProcessor()->removeParameter($sUri, $sName);
    
    if (!$bResult) {
      
      $this->throwException(txt('Cannot delete parameter %s', $sName));
    }
    
    return $bResult;
  }
  
  public function setParameters($aParams, $sUri = '') {
    
    foreach ($aParams as $sName => $sValue) $this->setParameter($sName, $sValue, $sUri);
  }
  
  public function setParameter($sName, $sValue, $sUri = '') {
    
    $bResult = $this->getProcessor()->setParameter($sUri, $sName, (string) $sValue);
    
    if (!$bResult) {
      
      $this->throwException(txt('Cannot create parameter %s', $sName));
    }
    
    return $bResult;
  }
  
  public function getParameter($sLocalName, $sUri = '') {
    
    $mResult = $this->getProcessor()->getParameter($sUri, $sLocalName);
    
    if (!$mResult) {
      
      $this->throwException(txt('Cannot retrieve parameter %s', $sName));
    }
    
    return $mResult;
  }
  
  protected function setProcessor(\XSLTProcessor $processor) {
    
    $this->processor = $processor;
  }
  
  private function getProcessor() {
    
    return $this->processor;
  }
  
  public function includeElement(dom\element $el, dom\element $ext = null) {
    
    $sPrefixes = 'extension-element-prefixes';
    
    if ($this->isEmpty()) {
      
      $this->throwException(txt('Cannot import document in empty template'));
    }
    
    if ($sResult = $el->getAttribute($sPrefixes)) {
      
      if ($sTarget = $this->getAttribute($sPrefixes)) {
        
        $aTarget = explode(' ', $sTarget);
        $aResult = $aPrefixes = array_diff(explode(' ', $sResult), $aTarget);
        
      } else {
        
        $aTarget = array();
        $aResult = $aPrefixes = explode(' ', $sResult);
      }
      
      foreach ($aPrefixes as $iPrefix => $sPrefix) {
        
        if (!$this->getNamespace($sPrefix)) {
          
          if ($sNamespace = $el->getNamespace($sPrefix)) {
            
            // TODO to add a namespace
            $this->setAttribute($sPrefix.':ns', 'null', $sNamespace); 
            // $this->setAttribute('xmlns:'.$sPrefix, $sNamespace);
            
          } else unset($aResult[$iPrefix]);
        }
      }
      
      $this->setAttribute($sPrefixes, implode(' ', array_merge($aResult, $aTarget)));
      
      if ($ext) {
        
        switch ($ext->getName(true)) {
          
          case 'include' : $ext->replace($el->getChildren()); break;
          case 'import' : $this->add($el->getChildren()); break;
          
          default : 
            
            $this->throwException(txt('Cannot import document in empty template with %s', $ext->asToken()));
        }
      }
      else {
        
        $this->shift($el->getChildren());
      }
    }
  }
  
  public function includeExternal(parser\xslt\Handler $template, dom\element $external = null, array $aMarks = array(), array &$aPaths = array(), $iLevel = 0) {
    
    if ($template->isEmpty()) {
      
      $this->throwException(t('Cannot import document in empty template'));
    }
    
    $template->includeExternals($aPaths, $iLevel + 1);
    
    /*foreach ($aMarks as $eMark) { // mark elements with filename

      foreach ($template->query('//la:*') as $el)
        $el->setAttribute('file-source', (string) $template->getFile());
    }*/
    
    $this->includeElement($template->getRoot(), $external);
  }
  
  public function includeExternals(array &$aPaths = array(), $iLevel = 0) {
    
    $dom = $this->getControler();
    
    $iMaxLevel = $dom->readArgument('import-depth');
    
    if ($iLevel > $iMaxLevel) {
      
      $this->throwException(t('Too much recursion when importing'));
      
    } else {
      
      $imports = $dom->create('collection', array($this->getRoot()->queryByName('include', self::NS)));
      $imports->addArray($this->getRoot()->queryByName('import', self::NS));
      
      if ($imports->length) {
        
        //if ($this->getFile()) $aPaths[] = (string) $this->getFile();
        //$aMarks = $this->query('le:mark', array('le' => SYLMA_NS_EXECUTION)); // look for mark elements source
        
        $aPaths = $aMarks = array();
        
        foreach ($imports as $href) {
          
          if ($file = $this->buildExternal($href, $aPaths)) {
            
            $template = new self((string) $file, \Sylma::MODE_EXECUTION);
            $this->includeExternal($template, $href, $aMarks, $aPath, $iLevel);
          }
          
          $href->remove();
        }
      }
    }
  }
  
  protected function retrieveErrors() {
    
    $aErrors = libxml_get_errors();
    
    if ($aErrors) { // TODO, nice view
      
      foreach ($aErrors as $error) {
        
        $this->throwException(txt($error->message));
      }
    }
  }
  
  public function parseDocument(dom\handler $doc, $bXML = true) { // WARNING, XML_Document typed can cause crashes
    
    $mResult = null;
    $dom = $this->getControler();
    
    if ($doc->isEmpty()) {
      
      $doc->throwException(t('Cannot parse empty document'));
    }
    
    if ($this->isEmpty()) {
      
      $this->throwException(t('Cannot parse empty template'));
    }
    
    $this->includeExternals();
    
    libxml_use_internal_errors(true);
    
    $this->getProcessor()->importStylesheet($this->getDocument());
    
    if ($bXML) {
      
      $mResult = $this->getProcessor()->transformToDoc($doc->getDocument());
      
      if ($mResult && $mResult->documentElement) {
        
        $mResult = $dom->create('handler', array($mResult));
      }
      else {
        
        $mResult = null;
      }
    }
    else {
      
      $mResult = $this->getProcessor()->transformToXML($doc->getDocument());
    }
    
    $this->retrieveErrors();
    
    libxml_clear_errors();
    libxml_use_internal_errors(false);
    
    $dom->addStat('parse', array($this, $doc));
    
    return $mResult;
  }
}