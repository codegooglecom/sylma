<?php

function t($s) {
  
  return $s;
}

function txt() {
  
  $sResult = '';
  
  if (func_num_args()) {
    
    $aArguments = func_get_args();
    $sContent = array_shift($aArguments);
    
    if (count($aArguments)) $sResult = vsprintf(t($sContent), $aArguments);
    else $sResult = t($sContent);
  }
  
  return $sResult;
}