<?php

namespace sylma\core\functions\text;

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

/**
 * Quote and escape one string or array of strings
 */
function addQuote($mValue) {

  if (is_array($mValue)) {

    foreach ($mValue as &$mSubValue) $mSubValue = addQuote($mSubValue);
    return $mValue;

  } else if ($sResult = (string) $mValue) return "'".addslashes($sResult)."'";
  else return null;
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

