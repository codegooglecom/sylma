<?php

class Utils {
  
  /**
   * Clean a document in file system from extra namespaces
   * @param string $sPath The absolute path to the document
   */
  public static function cleanDocument($sPath) {
    
    $oDocument = new XML_Document(Controler::getAbsolutePath($sPath));
    $oDocument->updateAllNamespaces();
    
    $oDocument->save();
    
    dspm(xt('Document %s nettoyé !', view($oDocument)), 'success');
  }
  
  /**
   * Repeat an action call a number of time limited by time and/or max count
   * @param string $sPath The path to the action to test
   * @param integer $iMaxTime The maximum time the action should be called, if the max time is reached
   * the execution will stop after the current call has finished and a resume will be send as messages
   */
  public static function testAction($sPath, $iMaxTime = 10, $iMaxCount = 50) {
    
    $iCount = $iMaxCount;
    
    if ($iCount > 1000) {
      
      dspm(xt('%s récurences est un chiffre trop élévé, remplacement par %s',
        new HTML_Strong($iCount), new HTML_Strong(50)), 'warning');
      $iCount = 50;
    }
    
    if ($iMaxTime > 60) {
      
      dspm(xt('%s s de temps maximum est un chiffre trop élévé, remplacement par %s',
        new HTML_Strong($iCount), new HTML_Strong(10)), 'warning');
      $iMaxTime = 10;
    }
    
    $iCalls = 0;
    $iBiggerTime = 0;
    
    $oAction = null;
    $oResult = new XML_Document('root');
    
    $iStart = microtime(true);
    
    $oPath = new XML_Path($sPath, array(), true, true, false);
    if (!$oPath->getPath()) {
      
      dspm(xt('L\'action %s n\'existe pas !', new HTML_Strong($sPath)), 'warning');
    }
    else {
      
      while ($iCount) {
        
        $oAction = new XML_Action($sPath);
        $iActionTime = microtime(true);
        
        $oResult->add($oAction); // parse
        
        $iDeltaTime = microtime(true) - $iActionTime;
        if ($iDeltaTime > $iBiggerTime) $iBiggerTime = $iDeltaTime;
        
        if (microtime(true) - $iStart > $iMaxTime) $iCount = 0;
        else {
          
          $iCount--;
          $iCalls++;
        }
      }
      
      $iTotalTime = microtime(true) - $iStart;
      $iAverageTime = $iTotalTime / $iCalls;
      $iDeltaTime = ((100 / $iAverageTime) * ($iBiggerTime - $iAverageTime));
      
      $oCalls = new HTML_Strong($iCalls);
      if ($iCalls != $iMaxCount) $oCalls->setAttribute('style', 'color : red');
      
      dspm(t('Test terminé'), 'success');
      dspm(xt('Action : %s', new HTML_Strong($sPath)));
      dspm(xt('Temps total : %s', new HTML_Strong(number_format($iTotalTime, 3).' s')));
      dspm(xt('Nombre d\'appels : %s', $oCalls));
      dspm(new HTML_Hr());
      dspm(xt('Mémoire utilisée : %s', new HTML_Strong(formatMemory(memory_get_peak_usage()))));
      dspm(xt('Temps moyen : %s', new HTML_Strong(number_format($iAverageTime, 3).' s')));
      dspm(xt('Variation : %s%%', new HTML_Strong(number_format($iDeltaTime, 1))));
    }
    
    return $oAction;
  }
}