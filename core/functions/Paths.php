<?php

function extractDirectory($sPath, $bObject = true) {
  
  $sPath = substr($sPath, strlen(getcwd().MAIN_DIRECTORY) + 1);
  if (SYLMA_XAMPP_BUG && Sylma::isWindows()) $sPath = str_replace('\\', '/', $sPath);
  else if (preg_match("/Win/", getenv("HTTP_USER_AGENT" ))) $sPath = str_replace('\\', '/', $sPath);
  
  $sResult = substr($sPath, 0, strlen($sPath) - strlen(strrchr($sPath, '/')));
  
  if ($bObject) return Controler::getDirectory($sResult);
  else return $sResult;
}

function extract_directory($sFile, $bObject = true) {
  
  $sFile = substr($sFile, strlen(getcwd().MAIN_DIRECTORY) + 1);
  if (Sylma::isWindows()) $sFile = str_replace('\\', '/', $sFile);
  
  $sResult = substr($sFile, 0, strlen($sFile) - strlen(strrchr($sFile, '/')));
  
  if ($bObject) {
    
    // object
    if (!$fs = Sylma::getControler('fs')) {
      
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

function pathWin2Unix($sPath) {
  
  return str_replace('\\', '/', $sPath);
}

function path_absolute($sTarget, $mSource = '', $sChar = '/') {
  
  if (!$sTarget || $sTarget{0} == $sChar) return $sTarget;
  else {
    
    return $mSource . $sChar . $sTarget;
  }
}
