<?php

namespace sylma\modules\formater;
use \sylma\core, \sylma\dom;

require_once('core/module/Domed.php');

class Controler extends core\module\Domed {
  
  public function __construct() {
    
    $this->setDirectory(__file__);
  }
  
  protected function loadObject($val) {
    
    $result = null;
    
    if ($val instanceof dom\handler) {
      
      $result = $val;
    }
    if ($val instanceof core\argumentable) {
      
      $arg = $val->asArgument();
      $result = $this->loadArgument($arg);
    }
    else if ($val instanceof dom\domable) {
      //dspf(get_class($val)); return null;
      $result = $val->asDOM();
    }
    
    return $result;
  }
  
  protected function loadArgument(core\argument $arg) {
    
    return $this->loadObject($arg);
  }
  
  public function asHTML($mVal) {
    
    $result = null;
    
    if (is_object($mVal)) {
      
      $doc = $this->loadObject($mVal);
    }
    else {
      
      //$result = '[' . gettype($mVal) . ']';
    }
    
    if ($doc) {
      
      $template = $this->getTemplate('default.xsl');
      $result = $template->parseDocument($doc);
    }
    
    return $result;
  }
  
  public function asToken($mVal) {
    
    $sResult = '[unknown]';
    
    if (is_string($mVal)) {
      
      $sResult = '[string] ' . $this->limitString($mVal);
    }
    else if (is_object($mVal)) {
      
      $sResult = '[object] ' . get_class($mVal);
    }
    else if (is_array($mVal)) {
      
      $sResult = '[array] ' . '@length ' . count($mVal);
    }
    else if (is_null($mVal)) {
      
      $sResult = '[null] ';
    }
    
    return '@var ' . $sResult;
  }
  
  public function limitString($mValue, $iLength = 50, $bXML = false) {
    
    $sValue = (string) $mValue;
    
    if (strlen($sValue) > $iLength) $sValue = substr($sValue, 0, $iLength).'...';
    
    if ($bXML) {
      
      $iLastSQuote = strrpos($sValue, '&');
      $iLastEQuote = strrpos($sValue, ';');
      
      if (($iLastSQuote) && ($iLastEQuote < $iLastSQuote)) $sValue = substr($sValue, 0, $iLastSQuote).'...';
    }
    
    return $sValue;
  }
  
  public function getBacktrace() {
  
    foreach (debug_backtrace() as $aLine) {
      
      echo $aLine['file'].' - '.$aLine['class'].'::'.$aLine['function'] . ' - ' . $aLine['line'] . '<br/>';
    }
  }
}