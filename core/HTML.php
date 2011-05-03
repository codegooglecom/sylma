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
    //echo 'hello : '.$this->getName().' / ';
  }
  
  public function __toString() {
    
    $this->parse();
    return parent::__toString(true);
  }
}

/**
 * Attribut
 */
class HTML_Attribute extends DOMAttr {
  
  var $sNamespace = '';
  var $sName = '';
  var $oValue = '';
  
  public function __construct($sName = '', $oValue = '', $sNamespace = '') {
    
    $this->sName = (string) $sName;
    $this->oValue = $oValue;
    $this->setNamespace($sNamespace);
  }
  
  public function setNamespace($sValue = '') {
    
    $this->sNamespace = $sValue;
  }
  
  public function getNamespace() {
    
    return $this->sNamespace;
  }
  
  public function setValue($sValue = '') {
    
    $this->oValue = $sValue;
  }
  
  public function getValue() {
    
    return $this->oValue;
  }
  
  public function __toString() {
    
    if ($this->sName) {
      
      $sValue = $this->oValue; // htmlentities((string) $this->oValue, ENT_COMPAT, 'UTF-8')
      
      if ($this->getNamespace()) $sName = $this->getNamespace().':'.$this->sName;
      else $sName = $this->sName;
      
      return $sName.'="'.$sValue.'"';
    }
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

class HTML_Document extends XML_Helper {
  
  private $oHead = null;
  private $sOnLoad = '';
  
  public function __construct($sPath = '') {
    
    // $imp = new DomImplementation;
    // $dtd = $imp->createDocumentType('html', '-//W3C//DTD XHTML 1.0 Transitional//EN', 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd');
    
    parent::__construct($sPath);
    //'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
  }
  
  public function addOnLoad($sContent) {
    
    $this->sOnLoad .= "\n".$sContent;
  }
  
  public function addJS($sHref, $sContent = null) {
    
    if ($oHead = $this->getHead()) {
      
      if ($sContent) $oHead->add(new HTML_Script('', $sContent));
      else if (!$oHead->get("ns:script[@src='$sHref']")) $oHead->add(new HTML_Script($sHref));
    }
  }
  
  public function getHead() {
    
    if (!$this->oHead && ($oHead = $this->get('/xhtml:html/xhtml:head', array('xhtml' => SYLMA_NS_XHTML)))) $this->oHead = $oHead;
    
    return $this->oHead;
  }
  
  public function addCSS($sHref = '') {
    
    if (($oHead = $this->getHead()) && !$oHead->get("ns:link[@href='$sHref']"))
      $oHead->add(new HTML_Style($sHref));
  }
  
  public function addIECSS($sHref = '', $sVersion = '') {
    
    // $this->getBloc('header')->add(new HTML_IEComment(new HTML_Style($sHref), $sVersion));
  }
  
  public function __toString() {
    
    // $imp = new DomImplementation;
    // $dtd = $imp->createDocumentType('html', '-//W3C//DTD XHTML 1.0 Transitional//EN', 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd');
    // $doc = $imp->createDocument('', '', $dtd);
    // $doc->loadXML(parent::__toString());
    
    // return $doc->saveHTML();
    
    // $oRoot = $doc->importNode($this->getRoot(), true);
    // $this->appendChild($oRoot);
    // echo $oRoot;
    // return $doc->saveXML();
    
    $sDocType = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
    //$this->formatOutput = true;
    // return $this->saveHTML();
    
    if ($this->sOnLoad) $this->addJS(null, "window.addEvent('domready', function() {\n".$this->sOnLoad."\n});");
    
    if ($oElements = $this->query(SYLMA_HTML_TAGS, array('html' => SYLMA_NS_XHTML)))
      foreach ($oElements as $oElement) if (!$oElement->hasChildren()) $oElement->set(' ');
    
    $oView = new XML_Document($this);
    
    $oView->query('//@ls:owner | //@ls:mode | //@ls:group', 'ls', SYLMA_NS_SECURITY)->remove();
    $oView->formatOutput();
    
    return $sDocType."\n".$oView->display(false);
  }
}

class HTML_Icone extends HTML_A {
  
  public function __construct($sHref = '', $sSrc = '', $sTitle = '', $aAttributes = array()) {
    
    parent::__construct($sHref, '', $aAttributes);
    $this->add(new HTML_Img($sSrc, $sTitle, array('title' => $sTitle)));
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

class HTML_Comment {
  
  private $oValue = '';
  
  public function __construct($oValue = '') {
    
    $this->oValue = $oValue;
  }
  
  public function __toString() {
    
    return '<!--'.$this->oValue.'-->';
  }
}

class HTML_IEComment extends HTML_Comment {
  
  public function __construct($oValue, $sVersion = '') {
    
    parent::__construct("[if IE$sVersion]>$oValue<![endif]");
  }
}

class HTML_A extends HTML_Tag {
  
  public function __construct($sHref = '', $mChild = '', $aAttributes = array()) {
    
    parent::__construct('a', $mChild, $aAttributes);
    $this->setAttribute('href', $sHref);
    //$this->forceClosure();
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

class HTML_Img extends HTML_Tag {
  
  public function __construct($sSrc, $sAlt = '', $aAttributes = array()) {
    
    parent::__construct('img', null, $aAttributes);
    $this->setAttribute('src', $sSrc);
    $this->setAttribute('alt', $sAlt);
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

class HTML_P extends HTML_Tag {
  
  public function __construct($oChildren = null, $aAttributes = array()) {
    
    parent::__construct('p', $oChildren, $aAttributes);
  }
}

class HTML_Hr extends HTML_Tag {
  
  public function __construct() {
    
    parent::__construct('hr');
  }
}

/*
 * Divers
 **/

class HTML_Table extends HTML_Tag {


  function __construct() {
    
    parent::__construct('table');
  }

  function addRow() {
    
    $row = new HTML_TableRow();
    $this->add($row); 
    
    return $row;
  }
}

class HTML_TableRow extends HTML_Tag {


  function __construct() {
    
    parent::__construct('tr');
  }

  function addCell($content, $attributes = NULL) {
    
    $cell = new HTML_Tag('td');
    $this->add($cell);
    
    $cell->add($content);
    
    if (is_array($attributes)) foreach ($attributes as $key => $val) $cell->setAttribute($key, $val);
    
    return $cell;
  }

  function addMultiCell($cells) {
    
    if (is_array($cells)) {
    
      foreach($cells as $key => $val) {
        
        if (is_array($val)) $this->addCell($key, $val);
        else $this->addCell($val);
      }
    }
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

class HTML_Dl extends HTML_Tag {
  
  function __construct($mChildren = '', $aAttributes = array(), $aChildKeyAttributes = array(), $aChildValueAttributes = array()) {
    
    parent::__construct('dl', $aAttributes);
    
    if (is_array($mChildren)) foreach ($mChildren as $sKey => $sValue) $this->addItem($sKey, $sValue, $aChildKeyAttributes, $aChildValueAttributes);
    else $this->addItem($mChildren, '', $aChildKeyAttributes);
  }
  
  function addItem($sKey, $sValue, $aKeyAttributes = array(), $aValueAttributes = array()) {
    
    $oKey = new HTML_Tag('dt', $sKey, $aKeyAttributes);
    $oValue = new HTML_Tag('dd', $sValue, $aValueAttributes);
    
    $this->add($oKey);
    $this->add($oValue);
    
    return array($oKey, $oValue);
  }
}


class HTML_Embed extends HTML_Tag {
  
  function __construct($sUrl = '', $iWidth = 0, $iHeight = 0, $sBgColor = '#ffffff', $aAttributes = array()) {
    
    parent::__construct('embed', $aAttributes);
    
    $this->setAttributes(array(
      
      'src'         => $sUrl,
      'quality'     => 'high',
      'bgcolor'     => $sBgColor,
      'width'       => $iWidth,
      'height'      => $iHeight,
      'type'        => 'application/x-shockwave-flash',
      'pluginspage' => 'http://www.macromedia.com/go/getflashplayer',
    ));
  }
}

