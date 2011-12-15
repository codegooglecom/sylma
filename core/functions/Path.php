<?php

namespace sylma\core\functions\path;

function extractDirectory($sFile, $bObject = true) {
  
  $sFile = substr($sFile, strlen(getcwd().MAIN_DIRECTORY) + 1);
  if (\Sylma::isWindows()) $sFile = str_replace('\\', '/', $sFile);
  
  $sResult = substr($sFile, 0, strlen($sFile) - strlen(strrchr($sFile, '/')));
  
  if ($bObject) {
    
    // object
    if (!$fs = \Sylma::getControler('fs')) {
      
      Sylma::throwException(txt('File controler not yet loaded. Cannot extract path %s', $sFile));
    }
    
    return $fs->getDirectory($sResult);
    // return \Controler::getDirectory($sResult);
  }
  else {
    
    // string
    return $sResult;
  }
}

function winToUnix($sPath) {
  
  return str_replace('\\', '/', $sPath);
}

function toAbsolute($sTarget, $mSource = '', $sChar = '/') {
  
  if (!$sTarget || $sTarget{0} == $sChar) return $sTarget;
  else {
    
    return $mSource . $sChar . $sTarget;
  }
}
