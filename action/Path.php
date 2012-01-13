<?php

class XML_Path {
  
  private $aArguments = array('index' => array(), 'assoc' => array());
  
  private $sPath = '';
  private $sOriginalPath = '';
  private $sSimplePath = '';
  
  private $sExtension = '';
  
  private $oFile = null;
  
  /**
   * @param string $sPath Path to look for an action
   * @param array $aArguments List of any arguments to add to the path
   * @param boolean $bParse Look for the correct file path through directories
   * @param $bArguments Use of indexed arguments (file/argument1/argument2)
   */
  
  public function __construct($sPath, array $aArguments = array(), $bParse = true, $bArguments = true, $bDebug = true) {
    
    // Remove arguments following '?' of type ..?arg1=val&arg2=val..
    
    $sPath = str_replace('__', '..', $sPath); // tmp until parseGet ^ available
    
    if ($iAssoc = strpos($sPath, '?')) {
      
      $sAssoc = substr($sPath, $iAssoc + 1);
      $sPath = substr($sPath, 0, $iAssoc);
      
      $aAssoc = explode('&', $sAssoc);
      
      foreach ($aAssoc as $sArgument) {
        
        $aArgument = explode('=', $sArgument);
        
        if (count($aArgument) == 1) $aArguments[] = $this->parseBaseType($aArgument[0]); // index : only name
        else $aArguments[$aArgument[0]] = $this->parseBaseType($aArgument[1]); // assoc : name and value
      }
    }
    
    // add assoc and index arguments
    
    if ($aArguments) {
      
      foreach ($aArguments as $sKey => $mArgument) {
        
        if (!$mArgument) {
          
          $mArgument = $sKey;
          $sKey = 0;
        }
        
        if (is_integer($sKey)) $this->aArguments['index'][] = $mArgument;
        else $this->aArguments['assoc'][$sKey] = $mArgument;
      }
    }
    
    $this->sOriginalPath = $sPath;
    $this->setPath($sPath);
    $this->sSimplePath = $sPath;
    
    if ($bParse) $this->parsePath($bArguments, $bDebug);
  }
  
  public function parsePath($bArguments = true, $bDebug = true) {
    
    global $aActionExtensions;
    
    $sResultPath = '';
    $bError = false;
    $bUseIndex = false;
    
    $oDirectory = Controler::getDirectory();
    $oFile = null;
    
    if ($this->getPath() == '/') $aPath = array();
    else {
      
      $aPath = explode('/', $this->getPath());
      array_shift($aPath);
    }
    
    do {
      
      $sSubPath = $aPath ? $aPath[0] : '.';
      
      if (!$oSubDirectory = $oDirectory->getDirectory($sSubPath)) {
        
        if (!$bArguments) $bError = true;
        
        // look for executable files with $aActionExtensions array of executable extensions
        
        foreach ($aActionExtensions as $sExtension) {
          
          if ($oFile = $oDirectory->getFile($sSubPath.$sExtension, false)) {
            
            $bError = false;
            break;
          }
        }
        
        if ($bError && $bDebug) {
          
          $this->dspm(xt('Aucun fichier correspondant à %s dans %s',
            new HTML_Strong($sSubPath),
            new HTML_Strong($oDirectory)), 'action/warning');
        }
        
      } else $oDirectory = $oSubDirectory;
      
      if (!$oFile && (!$aPath || !$oSubDirectory)) {
        
        if (($oFile = $oDirectory->getFile('index.eml')) || ($oFile = $oDirectory->getFile('index.iml'))) $bUseIndex = true;
        else if ($oDirectory->checkRights(MODE_EXECUTION)) {
          
          $bError = true;
          dspm(xt('Pas de fichier index dans "%s"', new HTML_Strong((string) $oDirectory)), 'action/warning');
          
        } else {
          
          $bError = true;
          dspm(xt('Le répertoire "%s" ne peut pas être listé, droits insuffisants', new HTML_Strong($oDirectory)), 'action/warning');
        }
        
      } else array_shift($aPath);
      
    } while (!$oFile && !$bError);
    
    if (!$bError) {
      
      if ($bUseIndex) $this->sOriginalPath = (string) $oFile->getParent();
      else $this->sOriginalPath = (string) $oFile;
      
      // if ($sExtension = $this->getExtension()) $this->sOriginalPath .= '.'.$sExtension;
      
      // remove empty arguments
      
      $aTempPath = $aPath;
      $aPath = array();
      
      foreach ($aTempPath as $sValue) {
        
        if ($sValue) $aPath[] = $this->parseBaseType($sValue);
      }
      
      // push final values
      
      $this->setFile($oFile);
      $this->pushIndex($aPath);
      $this->setPath($oFile);
      
      $this->sSimplePath = $oFile->getActionPath().$this->getStringIndex(false); // TODO add assoc
      
    } else $this->setPath('');
  }
  
  public function parseBaseType($mValue) {
    
    $mResult = $mValue;
    
    if (is_string($mValue) && strpos($mValue, 'xs:') !== false) {
      
      preg_match('/^xs:(\w+)\(([^\)]+)\)$/', $mValue, $aMatches);
      
      switch ($aMatches[1]) {
        
        case 'bool' :
        case 'boolean' :
          
          $mResult = strtobool($aMatches[2]);
          
        break;
        
        case 'int' :
        case 'integer' :
          
          $mResult = (int) $aMatches[2];
          
        break;
        
        default :
          
          $this->dspm(xt('Unknown base type %s', new HTML_Strong($aMatches[1])), 'warning');
      }
    }
    
    return $mResult;
  }
  
  public function parseExtension($bRemove) {
    
    $sPath = $this->getPath();
    
    preg_match('/\.(\w+)$/', $sPath, $aResult, PREG_OFFSET_CAPTURE);
    
    if (count($aResult) == 2 && ($sExtension = $aResult[1][0])) {
      
      $iExtension = $aResult[1][1];
      if ($bRemove) $this->setPath(substr($sPath, 0, $iExtension - 1).substr($sPath, $iExtension + strlen($sExtension)));
      
      $this->sExtension = $sExtension;
    }
    
    return $this->getExtension();
  }
  
  public function getDirectory() {
    
    if ($this->getFile()) return $this->getFile()->getParent();
    else return null;
  }
  
  public function getFile() {
    
    return $this->oFile;
  }
  
  public function setFile(XML_File $oFile) {
    
    $this->oFile = $oFile;
  }
  
  public function setPath($mPath) {
    
    $this->sPath = (string) $mPath;
  }
  
  public function getActionPath() {
    
    return $this->getFile()->getActionPath();
  }
  
  public function getSimplePath() {
    
    return $this->sSimplePath;
  }
  
  public function getOriginalPath() {
    
    return $this->sOriginalPath;
  }
  
  public function isValid() {
    
    return (bool) $this->getPath();
  }
  
  public function getPath() {
    
    return $this->sPath;
  }
  
  public function getExtension() {
    
    return $this->sExtension;
  }
  
  public function setArgument($sArgument, $aArgument = array()) {
    
    if (is_array($aArgument)) $this->aArguments[$sArgument] = $aArgument;
    else dspm(xt('Liste d\'argument invalide, ce n\'est pas un tableau'), 'action/error');
  }
  
  public function getAllArguments($bFlat = false) {
    
    if ($bFlat) {
      
      $aResult = array();
      foreach ($this->aArguments as $aArguments) $aResult = array_merge($aResult, $aArguments);
      
      return $aResult;
      
    } else return $this->aArguments;
  }
  
  public function getArgument($sArgument) {
    
    if (array_key_exists($sArgument, $this->aArguments)) return $this->aArguments[$sArgument];
    else return null;
  }
  
  public function shiftIndex($mArguments) {
    
    if (is_array($mArguments)) $this->aArguments['index'] = array_merge($mArguments, $this->aArguments['index']);
    else array_unshift($mArguments, $this->aArguments['index']);
  }
  
  public function pushIndex($mArguments) {
    
    if (is_array($mArguments)) $this->aArguments['index'] = array_merge($this->aArguments['index'], $mArguments);
    else array_push($this->aArguments['index'], $mArguments);
  }
  
  private function setKey($sArray, $sKey, $mValue) {
    
    $this->aArguments[$sArray][$sKey] = $mValue;
    //else if (array_key_exists($sKey, $this->aArguments[$sArray])) unset($this->aArguments[$sArray][$sKey]);
  }
  
  public function setIndex($iKey, $mValue = '') {
    
    $this->setKey('index', $iKey, $mValue);
    if ($mValue) $this->aArguments['index'] = array_values($this->aArguments['index']);
  }
  
  public function setAssoc($sKey, $mValue = '') {
    
    $this->setKey('assoc', $sKey, $mValue);
  }
  
  public function mergeAssoc($aArguments) {
    
    $this->aArguments['assoc'] = array_merge($this->aArguments['assoc'], $aArguments);
  }
  
  public function getStringIndex($bRemove = true) {
    
    $aIndex = $this->aArguments['index'];
    if ($bRemove) $this->aArguments['index'] = array();
    
    if ($aIndex) return '/'.implode('/', $aIndex);
    else return '';
  }
  
  public function getIndex($iKey = 0, $bKeep = false) {
    
    $mResult = $this->getKey('index', $iKey, $bKeep);
    if ($mResult !== null) $this->aArguments['index'] = array_merge($this->aArguments['index']);
    
    return $mResult;
  }
  
  public function hasAssoc($sKey) {
    
    return array_key_exists($sKey, $this->aArguments['assoc']);
  }
  
  public function getAssoc($sKey, $bKeep = true) {
    
    return $this->getKey('assoc', $sKey, $bKeep);
  }
  
  private function getKey($sArray, $mKey, $bKeep) {
    
    if (array_key_exists($mKey, $this->aArguments[$sArray])) {
      
      $mResult = $this->aArguments[$sArray][$mKey];
      if (!$bKeep) unset($this->aArguments[$sArray][$mKey]);
      
      return $mResult;
    }
    
    return null;
  }
  
  public function viewResume() {
    
    $nPath = new XML_Element('path', null, array(), XML_Action::MONITOR_NS);
    
    if ($this->aArguments['index']) {
      
      foreach ($this->aArguments['index'] as $iKey => $mArgument) $nPath->addNode('argument', view($mArgument, false), array('index' => $iKey));
    }
    
    if ($this->aArguments['assoc']) {
      
      foreach ($this->aArguments['assoc'] as $sKey => $mArgument) $nPath->addNode('argument', view($mArgument, false), array('name' => $sKey));
    }
    
    return $nPath;
  }
  
  public function parse() {
    
    $sPath = (string) $this;
    return new HTML_A(Sylma::read('modules/editor/path').'?path='.$sPath, $sPath);
  }
  
  public function dspm($sMessage, $sStatut) {
    
    dspm(xt('%s : '.$sMessage, new HTML_Strong('XML_Path')), $sStatut);
  }
  
  public function __toString() {
    
    return $this->getPath();
  }
}




