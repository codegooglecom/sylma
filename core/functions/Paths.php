<?php

function extractDirectory($sPath, $bObject = true) {
  
  $sPath = substr($sPath, strlen(getcwd().MAIN_DIRECTORY) + 1);
  if (SYLMA_XAMPP_BUG && Sylma::isWindows()) $sPath = str_replace('\\', '/', $sPath);
  else if (preg_match("/Win/", getenv("HTTP_USER_AGENT" ))) $sPath = str_replace('\\', '/', $sPath);
  
  $sResult = substr($sPath, 0, strlen($sPath) - strlen(strrchr($sPath, '/')));
  
  if ($bObject) return Controler::getDirectory($sResult);
  else return $sResult;
}

function pathWin2Unix($sPath) {
  
  return str_replace('\\', '/', $sPath);
}

function path_absolute($sTarget, $mSource = '') {
  
  if (!$sTarget || $sTarget{0} == '/') return $sTarget;
  else {
    
    return $mSource.'/'.$sTarget;
  }
}
