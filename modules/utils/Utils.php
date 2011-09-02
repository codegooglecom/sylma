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
    
    $action = null;
    $result = null;
    
    $iStart = microtime(true);
    
    $path = new XML_Path($sPath, array(), true, true, false);
    
    if (!$path->getPath()) {
      
      dspm(xt('L\'action %s n\'existe pas !', new HTML_Strong($sPath)), 'warning');
    }
    else {
      
      while ($iCount) {
        
        $action = new XML_Action($sPath);
        $iActionTime = microtime(true);
        
        $result = $action->parse();
        
        $iDeltaTime = microtime(true) - $iActionTime;
        if ($iDeltaTime > $iBiggerTime) $iBiggerTime = $iDeltaTime;
        
        if (microtime(true) - $iStart > $iMaxTime) {
          
          $iCount = 0;
        }
        else {
          
          $iCount--;
          $iCalls++;
        }
      }
      
      $iTotalTime = microtime(true) - $iStart;
      
      if ($iCalls) {
        
        $iAverageTime = $iTotalTime / $iCalls;
        $iDeltaTime = ((100 / $iAverageTime) * ($iBiggerTime - $iAverageTime));
      }
      else {
        
        $iAverageTime = $iDeltaTime = 0;
      }
      
      $eCalls = new HTML_Strong($iCalls);
      if ($iCalls != $iMaxCount) $eCalls->setAttribute('style', 'color : red');
      
      dspm(t('Test terminé'), 'success');
      dspm(xt('Action : %s', new HTML_Strong($sPath)));
      dspm(xt('Temps total : %s', new HTML_Strong(number_format($iTotalTime, 3).' s')));
      dspm(xt('Nombre d\'appels : %s', $eCalls));
      dspm(new HTML_Tag('hr'));
      dspm(xt('Mémoire max. utilisée : %s', new HTML_Strong(formatMemory(memory_get_peak_usage()))));
      dspm(xt('Temps moyen : %s', new HTML_Strong(number_format($iAverageTime, 3).' s')));
      dspm(xt('Variation : %s%%', new HTML_Strong(number_format($iDeltaTime, 1))));
    }
    
    return $result;
  }
  
  public function analyzeScripts() {
    
    $aMethods = array();
    
    $dir = Controler::getDirectory();
    $aFiles = $dir->getFiles(array('php'), null, null);
    
    foreach ($aFiles as $file) {
      
      if ($sValue = $file->read()) {
        
        dspm(xt('Text found in file %s',
          $file->parse()
        ));
        
        preg_match_all('/->(\w+)\(/', $sValue, $aMatch);
        
        foreach ($aMatch[1] as $sMethod) {
          
          if (!array_key_exists($sMethod, $aMethods)) $aMethods[$sMethod] = 0;
          $aMethods[$sMethod]++;
        }
      }
    }
    
    asort($aMethods);
    
    // print_r($aMethods);
    dspf($aMethods);
    
    return xt('%s file(s) analysed', new HTML_Strong(count($aFiles)));
  }
  
  /**
   * Doesn't work yet
   */
  public function analyzeEncoding() {
    
    $aNamespaces = array();
    
    $dir = Controler::getDirectory();
    $aFiles = $dir->getFiles(array('yml', 'php', 'iml', 'eml'), null, null);
    // $aFiles = $dir->getFiles(array('sml', 'xsl', 'xml', 'xsd', 'cml', 'txt', 'htaccess'), null, null);
    
    foreach ($aFiles as $file) {
      
      $sContent = $file->read();
      
      if (!mb_detect_encoding($sContent, 'UTF-8', true)) {
        
        dspm(xt('The file %s is not utf-8', new HTML_Strong((string) $file)), 'warning');
      }
      else dspf(mb_detect_encoding($sContent, 'UTF-8', true));
    }
    
    return xt('%s file(s) analysed', new HTML_Strong(count($aFiles)));
  }
  
  public function analyzeDocuments() {
    
    $aNamespaces = array();
    
    $dir = Controler::getDirectory();
    $aFiles = $dir->getFiles(array('eml', 'iml', 'xsl', 'sml'), null, null);
    
    foreach ($aFiles as $file) {
      
      if (($doc = $file->getDocument()) && !$doc->isEmpty()) {
        
        $els = $doc->query('//*');
        
        dspm(xt('%s elements found in file %s',
          new HTML_Strong($els->length),
          $file->parse()
        ));
        
        foreach ($els as $el) {
          
          $sNamespace = $el->getNamespace();
          $sElement = $el->getName();
          
          if (!array_key_exists($sNamespace, $aNamespaces)) $aNamespaces[$sNamespace] = array();
          if (!array_key_exists($sElement, $aNamespaces[$sNamespace])) $aNamespaces[$sNamespace][$sElement] = 0;
          
          $aNamespaces[$sNamespace][$sElement]++;
          
          if (!$sNamespace) dspm(xt('No namespace defined for element %s', $el->getPath()));
        }
      }
    }
    
    foreach ($aNamespaces as &$aElements) asort($aElements);
    
    dspf($aNamespaces);
    
    return xt('%s file(s) analysed', new HTML_Strong(count($aFiles)));
  }
}