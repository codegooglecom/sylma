<?php

class XSL_Document extends XML_Document {
  
  private $oProcessor = null;
  
  public function __construct($mChildren = '', $iMode = MODE_READ, $bInclude = true) {
    
    $this->oProcessor = new XSLTProcessor;
    
    if ($mChildren) parent::__construct($mChildren, $iMode);
    else {
      
      parent::__construct(new XML_Element('xsl:stylesheet', null, array('xmlns' => SYLMA_NS_XHTML, 'version' => '1.0'), SYLMA_NS_XSLT), $iMode);
      
      //new XML_Element('output', array('method' => 'xml', 'encoding' => 'utf-8'), true, 'xsl'));
      // 'xmlns:fo'    => 'http://www.w3.org/1999/XSL/Format',
      // 'xmlns:axsl'  => 'http://www.w3.org/1999/XSL/TransformAlias',
    }//$this->includeExternals();
  }
  
  public function removeParameter($sLocalName, $sUri = '') {
    
    $bResult = $this->getProcessor()->removeParameter($sUri, $sLocalName);
    
    if (!$bResult) Controler::addMessage(xt('Suppression impossible du paramètre %s - [%s]', new HTML_Strong($sName), new HTML_Strong($sValue), new HTML_Strong($sUri)), 'xml/warning');
    return $bResult;
  }
  
  public function setParameters($aParams, $sUri = '') {
    
    foreach ($aParams as $sName => $sValue) $this->setParameter($sName, $sValue, $sUri);
  }
  
  public function setParameter($sName, $sValue, $sUri = '') {
    
    $bResult = $this->getProcessor()->setParameter($sUri, $sName, (string) $sValue);
    
    if (!$bResult) Controler::addMessage(xt('Création du paramètre %s impossible avec la valeur %s - [%s]', new HTML_Strong($sName), new HTML_Strong($sValue), new HTML_Strong($sUri)), 'xml/warning');
    return $bResult;
  }
  
  public function getParameter($sLocalName, $sUri = '') {
    
    $mResult = $this->getProcessor()->getParameter($sUri, $sLocalName);
    
    if (!$mResult) Controler::addMessage(xt('Aucun résultat pour le paramètre %s - [%s]', new HTML_Strong($sName), new HTML_Strong($sUri)), 'xml/warning');
    return $mResult;
  }
  
  private function getProcessor() {
    
    return $this->oProcessor;
  }
  
  /*public function includeExternals($sQuery = , $aNS = , $aAttributes = array('extension-element-prefixes'), &$aPaths = array(), $iLevel = 0) {
    
    return parent::includeExternals($sQuery, $aNS, $aAttributes, $aPaths, $iLevel);
    //dspf($this);
  }*/
  
  public function includeElement(XML_Element $oElement, XML_Element $oExternal = null) {
    
    $sPrefixes = 'extension-element-prefixes';
    
    if (!$oElement || $this->isEmpty()) {
      
      dspm(xt('Impossible d\'inclure l\'éléments %s dans le document vide %s',
        view($oElement),
        $this->getFile() ? $this->getFile()->parse() : new HTML_Em(t('[Pas de chemin]'))), 'xml/warning');
    }
    else {
      if ($sResult = $oElement->getAttribute($sPrefixes)) {
        
        if ($sTarget = $this->getAttribute($sPrefixes)) {
          
          $aTarget = explode(' ', $sTarget);
          $aResult = $aPrefixes = array_diff(explode(' ', $sResult), $aTarget);
          
        } else {
          
          $aTarget = array();
          $aResult = $aPrefixes = explode(' ', $sResult);
        }
        
        foreach ($aPrefixes as $iPrefix => $sPrefix) {
          
          if (!$this->getNamespace($sPrefix)) {
            
            if ($sNamespace = $oElement->getNamespace($sPrefix)) {
              
              // TODO to add a namespace
              $this->setAttribute($sPrefix.':ns', 'null', $sNamespace); 
              // $this->setAttribute('xmlns:'.$sPrefix, $sNamespace);
              
            } else unset($aResult[$iPrefix]);
          }
        }
        
        $this->setAttribute($sPrefixes, implode(' ', array_merge($aResult, $aTarget)));
      }
      
      if ($oExternal) {
        
        switch ($oExternal->getName(true)) {
          
          case 'include' : $oExternal->replace($oElement->getChildren()); break;
          case 'import' : $this->add($oElement->getChildren()); break;
        }
        
      } else $this->shift($oElement->getChildren());
    }
  }
  
  public function includeExternal(XSL_Document $oTemplate, XML_Element $oExternal = null, $aMarks = array(), &$aPaths = array(), $iLevel = 0) {
    
    if (!$oTemplate->isEmpty()) {
      
      $oTemplate->includeExternals($aPaths, $iLevel + 1);
      
      foreach ($aMarks as $eMark) { // mark elements with filename
        
        foreach ($oTemplate->query('//la:*', array('la' => $eMark->read())) as $eElement)
          $eElement->setAttribute('file-source', (string) $oTemplate->getFile());
      }
      
      $this->includeElement($oTemplate->getRoot(), $oExternal);
    }
  }
  
  public function includeExternals(&$aPaths = array(), $iLevel = 0) {
    
    $iMaxLevel = SYLMA_MAX_INCLUDE_DEPTH;
    
    if ($iLevel > $iMaxLevel) {
      
      dspm(xt('Trop de redondance lors de l\'importation dans %s', $this->getFile()->parse()), 'xml/warning');
      
    } else {
      
      $oExternals = $this->query('/*/xsl:include | /*/xsl:import', array('xsl' => SYLMA_NS_XSLT));
      
      if ($oExternals->length) {
        
        if ($this->getFile()) $aPaths[] = (string) $this->getFile();
        $aMarks = $this->query('le:mark', array('le' => SYLMA_NS_EXECUTION)); // look for mark elements source
        
        foreach ($oExternals as $oExternal) {
          
          if ($oFile = $this->buildExternal($oExternal, $aPaths)) {
            
            $oTemplate = new XSL_Document((string) $oFile, MODE_EXECUTION);
            $this->includeExternal($oTemplate, $oExternal, $aMarks, $aPath, $iLevel);
          }
          
          $oExternal->remove();
        }
      }
    }
  }
  
  public function parseDocument(XML_Document $oDocument, $bXML = true) { // WARNING, XML_Document typed can cause crashes
    
    $mResult = null;
    
    if ($oDocument && !$oDocument->isEmpty() && !$this->isEmpty()) {
      
      $this->includeExternals();
      
      $this->getProcessor()->importStylesheet($this);
      
      $sResult = $this->getProcessor()->transformToXML($oDocument);
      
      if (Controler::isAdmin() && libxml_get_errors()) { // TODO, nice view
        
        foreach (libxml_get_errors() as $oError) {
          //dspf(get_object_vars($oError));
          if ($oError->file) $sFile = '';
          else if ($this->getFile()) $sFile = $this->getFile()->parse();
          else $sFile = new HTML_Tag('em', 'Fichier inconnu !');
          //print_r($oError);
          dspm(xt('%s : %s - %s dans %s', new HTML_Strong('Libxml'), xmlize($oError->message), view($this), $sFile), 'warning');
        }
        
        libxml_clear_errors();
      }
      
      if ($bXML) {
        
        $mResult = strtoxml(substr($sResult, 22), array(), true); //, array(), true
        if ($mResult && $mResult->length == 1) $mResult = new XML_Document($mResult);
        
        //if ($mResult->isEmpty()) Controler::addMessage(array(t('Un problème est survenu lors de la transformation XSL !'),new HTML_Hr,$sResult), 'xml/warning');
        
      } else $mResult = $sResult;
      //} else $mResult = substr($sResult, 21);
      
      XML_Controler::addStat('parse');
    }
    
    return $mResult;
  }
}



