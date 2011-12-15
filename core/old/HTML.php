<?php
/**
 * Classes de génération de code HTML
 * Auteur : Rodolphe Gerber
 */

/*
 * Tag
 **/
class HTML_Tag extends XML_Element {
  
  private $aStyles = array();
  private $bForceClosure = false;
  
  public function __construct($sName = '', $oContent = '', $aAttributes = array()) {
    
    parent::__construct($sName, $oContent, $aAttributes, SYLMA_NS_XHTML);
  }
  
  public function addClasses() {
    
    foreach (func_get_args() as $sClassName) $this->addClass($sClassName);
    
    return func_get_args();
  }
  
  public function addStyle($sKey = '', $sValue = '') {
    
    if ($sKey && $sValue) $this->aStyles[$sKey] = new HTML__Style($sKey, $sValue);
    return ($sKey && $sValue);
  }
  
  public function addStyles($aStyles = array()) {
    
    if (is_array($aStyles) && $aStyles) foreach ($aStyles as $sKey => $sValue) $this->addStyle($sKey, $sValue);
    return $aStyles;
  }
  
  /// TEMPORAIRE ///
  public function addAttribute($sName, $sValue) {
    
    $this->setAttribute($sName, $sValue);
  }
  
  public function forceClosure($bValue = true) {
    
    $this->bForceClosure = $bValue;
  }
  
  public function setAttributes($aAttributes = array()) {
    
    if (is_array($aAttributes)) {
      
      if (!$aAttributes) $this->aAttributes = array();
      else foreach ($aAttributes  as $sKey => $sValue) $this->setAttribute($sKey, $sValue);
      
    } // else ERROR Liste invalide
  }
  
  public function parse() {
    
    if ($this->aStyles) {
      
      $sStyles = implode(';', $this->aStyles);
      if ($oStyle = $this->getAttribute('style')) $sStyles = $oStyle->value . ';' . $sStyles;
      $this->setAttribute('style', $sStyles);
    }
    
    // if ($this->bForceClosure && !$this->hasChildren()) $this->add(' ');
    if ($this->bForceClosure && !$this->read()) $this->add("\n");
  }
  
  public function __toString() {
    
    $this->parse();
    return parent::__toString(true);
  }
}

/*
 * Attribut style
 **/
class HTML__Style extends HTML_Tag {
  
  var $sName;
  var $content;
  
  public function __construct($sName, $content) {
    
    $this->name = $sName;
    $this->content = $content;
  }
  
  public function __toString() {
    
    return $this->name.' : '.$this->content.';';
  }
}

class HTML_A extends HTML_Tag {
  
  public function __construct($sHref = '', $mChild = '', $aAttributes = array()) {
    
    parent::__construct('a', $mChild, $aAttributes);
    $this->setAttribute('href', $sHref);
    //$this->forceClosure();
  }
}

/*** Real Tags ***/

class HTML_Script extends HTML_Tag {
  
  public function __construct($sSrc = '', $oChild = null) {
    
    $this->forceClosure();
    parent::__construct('script');
    
    $this->setAttribute('type', 'text/javascript');
    
    if ($sSrc) {
      
      $this->setAttribute('src', $sSrc);
      $this->forceClosure();
      $this->add(' ');
    } else $this->add($oChild);
  }
}

class HTML_Style extends HTML_Tag {
  
  public function __construct($sHref = '', $oChild = null) {
    
    //$this->forceClosure();
    
    if ($sHref) {
      
      parent::__construct('link');
      
      $this->setAttribute('rel', 'stylesheet');
      $this->setAttribute('href', $sHref);
      $this->setAttribute('media', 'all');
      
    } else {
      
      parent::__construct('style', $oChild);
    }
    
    $this->setAttribute('type', 'text/css');
  }
}

/*
 * Crée une image cliquable en attribuant 'title'
 **/
class HTML_Span extends HTML_Tag {
  
  public function __construct($oChild = '', $aAttributes = array()) {
    
    parent::__construct('span', $oChild, $aAttributes);
  }
}

class HTML_Div extends HTML_Tag {
  
  public function __construct($mChildren = '', $aAttributes = array()) {
    
    parent::__construct('div', $mChildren, $aAttributes);
    $this->forceClosure();
  }
}

class HTML_Strong extends HTML_Tag {
  
  public function __construct($oChild = '', $aAttributes = array()) {
    
    parent::__construct('strong', $oChild, $aAttributes);
    $this->forceClosure();
  }
}

class HTML_Em extends HTML_Tag {
  
  public function __construct($oChild = '', $aAttributes = array()) {
    
    parent::__construct('em', $oChild, $aAttributes);
  }
}

class HTML_Br extends HTML_Tag {
  
  public function __construct() {
    
    parent::__construct('br');
  }
}

class HTML_Ul extends HTML_Tag {
  
  function __construct($mChildren = '', $aAttributes = array(), $aChildAttributes = array()) {
    
    parent::__construct('ul', null, $aAttributes);
    $this->forceClosure();
    if (is_array($mChildren)) foreach ($mChildren as $oChild) $this->addItem($oChild, $aChildAttributes);
    else if ($mChildren) $this->addItem($mChildren, $aChildAttributes);
  }
  
  function addMultiItem() {
    
    $aArguments = func_get_args();
    
    return $this->addNode('li', $aArguments, null, SYLMA_NS_XHTML);
  }
  
  function addItem($sContent, $aAttributes = array()) {
    
    return $this->addNode('li', $sContent, $aAttributes, SYLMA_NS_XHTML);
  }
}

