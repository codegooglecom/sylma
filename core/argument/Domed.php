<?php

namespace sylma\core\argument;
use \sylma\core, \sylma\dom;

require_once('Iterator.php');
require_once('dom2/domable.php');

class Domed extends Iterator implements dom\domable {
  
  /**
   * Build an @class Options's object with this argument's array
   * 
   * @param dom\node $oRoot The root node to insert the results to
   * @param? dom\document|null $oSchema The schema that will be used by the Options object
   * @param? string $sPath An optional sub-path to extract the arguments from
   * 
   * @return ElementInterface The new builded node, containing the xml version of this array
   */
  public function getOptions(dom\document $schema = null, $sPath = '') {
    
    require_once('dom2\Argument.php');
    
    $doc = $this->getDocument();
    self::getElement($doc, $sPath);
    
    return new dom\Argument($doc, $schema);
  }
  
  public function asDOM($sNamespace = '') {
    
    if (!$sNamespace) $sNamespace = $this->getNamespace();
    
    if (!$sNamespace) {
      
      $this->throwException(t('No namespace defined for export as dom document'));
    }
    
    if (count($this->aArray) > 1) {
      
      $this->throwException(txt('Cannot build document with more than one root value with @namespace %s', $sNamespace));
    }
    
    $this->normalize();
    
    $result = self::buildDocument($this->aArray, $sNamespace);
    
    if (!$result || $result->isEmpty()) {
      
      $formater = \Sylma::getControler('formater');
      $this->throwException (txt('Invalid @interface dom\document when exporting with @namespace %s', $sNamespace));
    }
    
    return $result;
  }
  
  public static function buildDocument(array $aArray, $sNamespace) {
    
    $dom = \Sylma::getControler('dom');
    $doc = $dom->create('handler');
    
    self::buildNode($doc, $aArray, $sNamespace);
    
    return $doc;
  }
  
  public function getElement(dom\complex $parent, $sPath = '') {
    
    if ($sPath) $aArray = $this->get($sPath);
    else $aArray = $this->aArray;
    
    return self::buildNode($parent, $aArray);
  }
  
  private static function buildNode(dom\complex $parent, array $aArray, $sNamespace) {
    
    foreach ($aArray as $sKey => $mValue) {
      
      if ($mValue !== null) {
        
        if (is_integer($sKey)) {
          
          // when integer key use duplicated element's name
          
          $node = $parent;
        }
        else {
          
          if ($sKey[0] == '@') {
            
            $parent->setAttribute(substr($sKey, 1), $mValue);
            continue;
          }
          else if ($sKey[0] == '#') {
            
            foreach ($mValue as $mSubValue) {
              
              $node = $parent->addElement(substr($sKey, 1), null, array(), $sNamespace);
              
              if (is_array($mSubValue)) self::buildNode($node, $mSubValue, $sNamespace);
              else $node->add($mSubValue);
            }
            
            continue;
          }
          else {
            
            $node = $parent->addElement($sKey, null, array(), $sNamespace);
          }
        }
        
        if (is_array($mValue)) self::buildNode($node, $mValue, $sNamespace);
        else $node->add($mValue);
      }
    }
  }
  
  protected static function normalizeObject($val) {
    
    if ($val instanceof dom\node) {
      
      $mResult = $val;
    }
    else {
      
      $mResult = parent::normalizeObject($val);
    }
    
    return $mResult;
  }
}
