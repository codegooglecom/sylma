<?php

class XML_Controler extends Module {
  
  const NS = 'http://www.sylma.org/dom/controler';
  
  private static $aStats = array();
  public static $aQueries = array();      // Array of running actions
  
  public static function viewStats() {
    
    // self::addStat('load', 1); // precog ;)
    
    $result = new XML_Document;
    $result->addNode('statistics', null, null, self::NS);
    
    foreach (self::$aStats as $sKey => $iValue) {
      
      $result->addNode('category', $iValue, array('name' => $sKey));
    }
    
    return $result->parseXSL(new XSL_Document(Controler::getSettings('messages/statistic-template/@path'), MODE_EXECUTION));
  }
  
  public static function getStats() {
    
    return self::$aStats;
  }
  
  public static function addQuery($sQuery) {
    
    if (array_key_exists($sQuery, self::$aQueries)) self::$aQueries[$sQuery]++;
    else self::$aQueries[$sQuery] = 1;
    
    Controler::infosSetQuery($sQuery);
  }
  
  public static function addStat($sKey, $iWeight = 1) {
    
    if (!array_key_exists($sKey, self::$aStats)) self::$aStats[$sKey] = $iWeight;
    else self::$aStats[$sKey] += $iWeight;
  }
}
