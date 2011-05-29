<?php

function xt() {
  
  if (func_num_args()) {
    
    $aArguments = func_get_args();
    $sValue = array_shift($aArguments);
    
    if (count($aArguments) && Sylma::get('messages/format/enable')) return strtoxml(vsprintf(t($sValue), $aArguments));
    else return t($sValue);
  }
  
  return '';
}

function strtoxml($sValue, array $aNS = array(), $bMessage = false) {
  
  $mResult = null;
  $oDocument = new XML_Document;
  $sAttributes = '';
  
  if (!array_key_exists(0, $aNS)) $aNS[0] = SYLMA_NS_XHTML;
  
  foreach ($aNS as $sPrefix => $sUri) {
    
    if ($sPrefix) $sPrefix = 'xmlns:'.$sPrefix;
    else $sPrefix = 'xmlns';
    
    $sAttributes .= " $sPrefix=\"$sUri\"";
  }
  
  if ($oDocument->loadText('<div'.$sAttributes.'>'.$sValue.'</div>') &&
    $oDocument->getRoot() && !$oDocument->getRoot()->isEmpty()) {
    
    $mResult = $oDocument->getRoot()->getChildren();
    
  } else if ($bMessage) dspm(array(t('StrToXml : Transformation impossible'), new HTML_Hr, $sValue), 'xml/warning');
  
  return $mResult;
}

class XML_Helper extends XML_Document {
  
  private $aBlocs = array();
  
  public function __construct($mChildren = '') {
    
    if ($mChildren === '') $mChildren = new HTML_Div('', array('class' => 'helper'));
    
    parent::__construct($mChildren);
  }
  
  public function loadAction($sPath, $oRedirect = null) {
    
    return new Action($sPath, $oRedirect);
  }
  
  public function addAction($sPath, $oRedirect = null) {
    
    $this->add($this->loadAction($sPath, $oRedirect));
  }
  
  public function setBloc($sKey, $mValue) {
    
    if ($sKey) $this->aBlocs[$sKey] = $mValue;
    return $mValue;
  }
  
  public function addBloc($sKey, $oTarget = null) {
    
    if ($oTarget && $oTarget instanceof XML_Element) return $oTarget->add($this->getBloc($sKey));
    else return $this->add($this->getBloc($sKey));
  }
  
  public function getBloc($sKey) {
    
    if (!array_key_exists($sKey, $this->aBlocs)) {
      
      $oBloc = new XML_Element($sKey);
      $this->aBlocs[$sKey] = $oBloc;
    }
    
    return $this->aBlocs[$sKey];
  }
  
  public function parse() {
    
    if (!$this->isEmpty()) return $this->getRoot();
    else return null;
  }
}

class XML_Attribute extends DOMAttr implements NodeInterface {
  
  public function __construct($sName, $sValue) {
    
    $sValue = checkEncoding($sValue);
    
    parent::__construct($sName, $sValue);
  }
  
  public function isElement() {
    
    return false;
  }
  
  public function isText() {
    
    return false;
  }
  
  public function getPrevious() {
    
    return null;
  }
  
  public function getNext() {
    
    return null;
  }
  
  public function getPrefix() {
    
    return $this->prefix;
  }
  
  public function getName($bFull = false) {
    
    if ($bFull && $this->getPrefix()) return $this->getPrefix().':'.$this->name;
    else return $this->name;
  }
  
  public function read() {
    
    return $this->getValue();
  }
  
  public function getValue() {
    
    return $this->value;
  }
  
  public function getParent() {
    
    return $this->ownerElement;
  }
  
  public function useNamespace($sNamespace) {
    
    return $this->getNamespace() == $sNamespace;
  }
  
  public function getNamespace() {
    
    return $this->namespaceURI;
  }
  
  public function getDocument() {
    
    return $this->ownerDocument;
  }
  
  public function remove() {
    
    $this->ownerElement->removeAttributeNode($this);
  }
  
  public function set($sValue) {
    
    $this->value = (string) checkEncoding($sValue);
  }
  
  public function __toString() {
    
    return $this->getName(true).'="'.xmlize($this->value).'"';
  }
}

class XML_CData extends DOMCdataSection implements NodeInterface {
  
  public function setValue($mValue) {
    
    $this->data = (string) $mValue;
  }
  
  public function getValue() {
    
    return $this->data;
  }
  
  public function remove() {
    
    return $this->parentNode->removeChild($this);
  }
  
  public function isText() {
    
    return false;
  }
  
  public function getDocument() {
    
    return $this->ownerDocument;
  }
  
  public function getParent() {
    
    return $this->parentNode;
  }
  
  public function getNext() {
    
    return $this->nextSibling;
  }
  
  public function getPrevious() {
    
    return $this->previousSibling;
  }
  
  public function isElement() {
    
    return false;
  }
  
  public function messageParse() {
    
    return new HTML_Span((string) $this, array('class' => 'message-element'));
  }
  /*
  public function formatOutput($iLevel = 0) {
    
    return null;
  }
  */
  public function __toString() {
    
    return $this->data;
    //return "<![CDATA[\n".$this->data."]]>\n";
  }
}

class XML_Text extends DOMText implements NodeInterface {
  
  // private $aRights = array();
  
  public function __construct($mContent) {
    
    if (is_object($mContent)) {
      
      if (method_exists($mContent, '__toString')) $mContent = (string) $mContent;
      else {
        
        Controler::addMessage(xt('Object " %s " cannot be converted to string !', new HTML_Strong(get_class($mContent))), 'xml/error');
        $mContent = '';
      }
    }
    
    $mContent = checkEncoding($mContent);
    
    // if (!(is_string($mContent) || is_numeric($mContent))) $mContent = '';
    // if ($mContent === 0) $mContent = '00'; //dom bug ?
    
    parent::__construct($mContent);
  }
  
  public function getDocument() {
    
    return $this->ownerDocument;
  }
  
  public function getNext() {
    
    return $this->nextSibling;
  }
  
  public function getPrevious() {
    
    return $this->previousSibling;
  }
  
  public function getParent() {
    
    return $this->parentNode;
  }
  
  public function replace($mChild) {
    
    if (is_string($mChild)) $oChild = new XML_Text($mChild);
    else $oChild = $mChild;
    
    $this->insertBefore($oChild);
    $this->remove();
    return $oChild;
  }
  
  public function remove() {
    
    return $this->parentNode->removeChild($this);
  }
  /*
  public function formatOutput($iLevel = 0) {
    
    return null;
  }*/
  
  public function isText() {
    
    return true;
  }
  
  public function isElement() {
    
    return false;
  }
  
  public function messageParse() {
    
    return new HTML_Span((string) $this, array('class' => 'message-element'));
  }
  
  public function getValue() {
    
    return $this->nodeValue;
  }
  
  public function __toString() {
    
    try {
      
      return xmlize($this->nodeValue);
      
		} catch ( Exception $e ) {
      
			dspm('Text : '.$e->getMessage(), 'xml/error');
		}
  }
}

class XML_Comment extends DOMComment implements NodeInterface {
  
  // private $aRights = array();
  
  public function getDocument() {
    
    return $this->ownerDocument;
  }
  
  public function getParent() {
    
    return $this->parentNode;
  }
  
  public function getNext() {
    
    return $this->nextSibling;
  }
  
  public function getPrevious() {
    
    return $this->previousSibling;
  }
  
  public function remove() {
    
    return $this->parentNode->removeChild($this);
  }
  /*
  public function formatOutput($iLevel = 0) {
    
    return null;
  }*/
  
  public function isText() {
    
    return false;
  }
  
  public function isElement() {
    
    return false;
  }
  
  public function messageParse() {
    
    return new HTML_Span((string) $this, array('class' => 'message-comment'));
  }
  
  public function __toString() {
    
    return "<!--{$this->data}-->";
  }
}

class XML_Fragment extends DOMDocumentFragment { }


