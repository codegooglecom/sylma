<?php

namespace sylma\core\argument;
use \sylma\core;

require_once('Basic.php');

class Domed extends Basic {
  
  public function getDocument($sNamespace = '') {
    
    return new \XML_Document($this->getFragment());
  }
  
  public function getFragment($sNamespace = '') {
    
    if (count($this->aArray) > 1) $this->throwException(txt('Cannot build document with more than one root value with @namespace %s', $sNamespace));
    if (!$sNamespace) $sNamespace = $this->getNamespace();
    
    return self::buildFragment($this->aArray, $sNamespace);
  }
  
  /**
   * Build an @class Options's object with this argument's array
   * 
   * @param DOMNode $oRoot The root node to insert the results to
   * @param? DOMDocument|null $oSchema The schema that will be used by the Options object
   * @param? string $sPath An optional sub-path to extract the arguments from
   * 
   * @return ElementInterface The new builded node, containing the xml version of this array
   */
  public function getOptions(dom\node $root, dom\document $schema = null, $sPath = '') {
    
    self::getElement($root, $sPath);
    
    return new XML_Options(new XML_Document($root), $schema);
  }
  
  public static function buildDocument(array $aArray, $sNamespace) {
    
    return new \XML_Document(self::buildFragment($aArray, $sNamespace));
  }
  
  public static function buildFragment(array $aArray, $sNamespace) {
    
    $fragment = \XML_Document::createFragment($sNamespace);
    
    self::buildNode($fragment, $aArray);
    
    return $fragment;
  }
  
  public function getElement(dom\element $root, $sPath = '') {
    
    if ($sPath) $aArray = $this->get($sPath);
    else $aArray = $this->aArray;
    
    self::buildNode($root, $aArray);
  }
  
  private static function buildNode(dom\node $parent, array $aArray) {
    
    foreach ($aArray as $sKey => $mValue) {
      
      if ($mValue !== null) {
        
        if (is_integer($sKey)) {
          
          $node = $parent;
        }
        else {
          
          if ($sKey[0] == '@') {
            
            $parent->setAttribute(substr($sKey, 1), $mValue);
            continue;
          }
          else if ($sKey[0] == '#') {
            
            foreach ($mValue as $mSubValue) {
              
              $node = $parent->addNode(substr($sKey, 1));
              
              if (is_array($mSubValue)) self::buildNode($node, $mSubValue);
              else if ($mSubValue instanceof core\argument) self::buildNode($node, $mSubValue->query());
              else $node->add($mSubValue);
            }
            
            continue;
          }
          else {
            
            $node = $parent->addNode($sKey);
          }
        }
        
        if (is_array($mValue)) self::buildNode($node, $mValue);
        else if ($mValue instanceof SettingsInterface) self::buildNode($node, $mValue->query());
        else $node->add($mValue);
      }
    }
  }
}
