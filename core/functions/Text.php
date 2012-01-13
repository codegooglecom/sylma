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

/**
 * Check encoding and optionnaly return value in utf-8
 */
function checkEncoding($sContent) {
  
  if (Sylma::read('dom/encoding/check') && !mb_check_encoding($sContent, 'UTF-8')) {
    
    $sContent = utf8_encode($sContent); //t('EREUR D\' ENCODAGE'); TODO , result not always in utf-8
    dspm(xt('L\'encodage n\'est pas utf-8 %s', new HTML_Strong(stringResume($sContent))), 'xml/warning');
  }
  
  return $sContent;
}

