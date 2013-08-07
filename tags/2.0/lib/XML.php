<?php

include('XML_Document.php');
include('XML_Element.php');

function xt() {
  
  if (func_num_args()) {
    
    $aArguments = func_get_args();
    $sValue = array_shift($aArguments);
    
    if (count($aArguments) && FORMAT_MESSAGES) return strtoxml(vsprintf(t($sValue), $aArguments));
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

interface XML_Composante {
  
  // public function getValue();
  // public function formatOutput();
  public function isElement();
  public function isText();
  public function remove();
  public function getDocument();
  public function getParent();
  public function getPrevious();
  public function getNext();
  //public function messageParse();
  //public function __toString();
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

class XML_Attribute extends DOMAttr implements XML_Composante {
  
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

class XML_CData extends DOMCdataSection implements XML_Composante {
  
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

class XML_Text extends DOMText implements XML_Composante {
  
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

class XML_NodeList implements Iterator {
  
  private $aNodes = array();
  public $length = 0;
  protected $iIndex = 0;
  private $aStore = array();
  
  public function __construct($mValues = null) {
    
    if ($mValues) $this->add($mValues);
  }
  
  public function toArray($sMode = null) {
    
    $aResults = array();
    
    foreach ($this as $oNode) {
      
      switch ($sMode) {
        
        case 'id' : $aResults[$oNode->getAttribute('id')] = $oNode->getChildren()->toArray(); break;
        case 'name' : $aResults[] = $oNode->getName(); break;
        // case 'attribute' : $aResult[] = $oNode->getAttribute($sAttribute);
        case null :
          
          // if ($oNode->isEmpty()) $aResults[] = $oNode->getName();
          if ($oNode->isText()) $aResults[] = (string) $oNode;
          else $aResults[$oNode->getName()] = $oNode->getValue();
          
        break;
        
        default : $aResults[$oNode->getAttribute($sMode)] = $oNode->read();
      }
    }
    
    return $aResults;
  }
  
  public function getFirst() {
    
    return $this->item(0);
  }
  
  public function item($iKey) {
    
    if (array_key_exists($iKey, $this->aNodes)) return $this->aNodes[$iKey];
    else return null;
  }
  
  public function __call($sMethod, $aArguments) {
    
    foreach ($this->aNodes as $oNode) {
      
      if (method_exists($oNode, $sMethod)) {
        
        $aEvalArguments = array();
        for ($i = 0; $i < count($aArguments); $i++) $aEvalArguments[] = "\$aArguments[$i]";
        
        eval('$oResult = $oNode->$sMethod('.implode(', ', $aEvalArguments).');');
        
      } else Controler::addMessage(xt('NodeList : Méthode %s introuvable', new HTML_Strong($sMethod)), 'xml/error');
    }
  }
  
  public function rewind() {
    
    $this->iIndex = 0;
  }
  
  public function next() {
    
    $this->iIndex++;
  }
  
  public function key() {
    
    return $this->iIndex;
  }
  
  public function current() {
    
    if (array_key_exists($this->iIndex, $this->aNodes)) return $this->aNodes[$this->iIndex];
    else return null;
  }
  
  public function view() {
    
    $aResult = array();
    foreach ($this->aNodes as $oNode) $aResult[] = $oNode->view(true, true, false);
    
    return new HTML_Div($aResult);
  }
  
  public function valid() {
    
    return ($this->iIndex < count($this->aNodes));
  }
  
  public function addNode($mValue) {
    
    $this->aNodes[] = $mValue;
    $this->length++;
  }
  
  public function add($mValue) {
    
    if ($mValue) {
      
      if (is_array($mValue) || // TODO, bad test
        (is_object($mValue) && ($mValue instanceof DOMNodeList || $mValue instanceof DOMNamedNodeMap || $mValue instanceof Iterator))) {
        
        foreach ($mValue as $oNode) $this->addNode($oNode);
        
      } else $this->addNode($mValue);
    }
  }
  
  public function store() {
    
    $this->aStore[] = $this->iIndex;
  }
  
  public function restore() {
    
    $this->iIndex = array_pop($this->aStore);
  }
  
  public function reverse() {
    
    $this->aNodes = array_reverse($this->aNodes);
    $this->rewind();
  }
  
  public function implode($sSeparator = ' ') {
    
    $aResult = array();
    
    foreach ($this->aNodes as $oNode) {
      
      $aResult[] = $oNode; 
      $aResult[] = $sSeparator;
    }
    
    array_pop($aResult);
    return $aResult;
  }
  
  public function __toString() {
    
    return implode('', $this->implode());
  }
}

class XML_Comment extends DOMComment implements XML_Composante {
  
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
    
    if ($sResult = $oDB->query($sQuery, $this->getNamespaces())) {
      
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


