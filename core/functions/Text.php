<?php

namespace sylma\core\functions\text;

/**
 * Check encoding and optionnaly return value in utf-8
 */
/*
function checkEncoding($sContent) {

  if (Sylma::read('dom/encoding/check') && !mb_check_encoding($sContent, 'UTF-8')) {

    $sContent = utf8_encode($sContent); //t('EREUR D\' ENCODAGE'); TODO , result not always in utf-8
    dspm(xt('L\'encodage n\'est pas utf-8 %s', new HTML_Strong(stringResume($sContent))), 'xml/warning');
  }

  return $sContent;
}
*/
/**
 * Quote and escape one string or array of strings
 */
function addQuote($mValue) {

  if (is_array($mValue)) {

    foreach ($mValue as &$mSubValue) $mSubValue = addQuote($mSubValue);
    $mResult = $mValue;
  }
  else if ($sResult = (string) $mValue) {

    $mResult = "'".addslashes($sResult)."'";
  }
  else {

    $mResult = null;
  }

  return $mResult;
}

function stringResume($mValue, $iLength = 50, $bXML = false) {

  $sValue = (string) $mValue;

  if (strlen($sValue) > $iLength) $sValue = substr($sValue, 0, $iLength).'...';

  if ($bXML) {

    $iLastSQuote = strrpos($sValue, '&');
    $iLastEQuote = strrpos($sValue, ';');

    if (($iLastSQuote) && ($iLastEQuote < $iLastSQuote)) $sValue = substr($sValue, 0, $iLastSQuote).'...';
  }

  return $sValue;
}

function toggleCamel($sName, $bToCamel = true) {

  if ($bToCamel) {

    $aWords = explode('-', strtolower($sName));

    $sResult = array_shift($aWords);
    foreach ($aWords as $sWord) $sResult .= ucfirst(trim($sWord));
  }
  else {

    $sResult = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $sName));
  }

  return $sResult;
}
