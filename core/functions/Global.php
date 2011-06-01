<?php

function strtobool($sValue, $bDefault = null) {
  
  if (strtolower($sValue) == 'true') return true;
  else if (strtolower($sValue) == 'false') return false;
  else return $bDefault;
}

function booltostr($bValue) {
  
  return ($bValue) ? 'true' : 'false';
}

function booltoint($bValue) {
  
  return $bValue ? 1 : 0;
}

/**
 * Renvoie la première valeur non nulle envoyée en argument, si aucune, renvoie la dernière valeur
 */
function nonull_val() {
  
  foreach (func_get_args() as $mArg) {
    
    $mResult = $mArg;
    if ($mArg) return $mArg;
  }
  
  return $mResult;
}

/**
 * 'Quote' une chaîne, ou plusieurs dans un tableau
 */
function addQuote($mValue) {
  
  if (is_array($mValue)) {
    
    foreach ($mValue as &$mSubValue) $mSubValue = addQuote($mSubValue);
    return $mValue;
    
  } else if ($sResult = (string) $mValue) return "'".addslashes($sResult)."'";
  else return null;
}

/**
 * Formate le nombre donnée en argument au format prix (p.ex : 1'999.95)
 */
function formatPrice($fNumber) {
  
  if (is_numeric($fNumber)) return 'CHF '.number_format($fNumber, 2, '.', "'");
  else return '';
}

function formatMemory($size) {
  
  $aUnit = array('b','Kb','Mb','Gb','Tb','Pb');
  return round($size / pow(1024, ($iResult = floor(log($size,1024)))), 2).' '.$aUnit[$iResult];
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


/**
 * Un-Conversion in UTF-8 of the characters : & " < >
 */
function unxmlize($sString) {
  
  return htmlspecialchars_decode($sString);
}

/**
 * Conversion in UTF-8 of the characters : & " < >
 */
function xmlize($sString) {
  
  return htmlspecialchars($sString, ENT_COMPAT, 'UTF-8');
}

/**
 * Make a url readable value
 */
function urlize($sValue) {
  
  //$aFind = array('/[ÀÁÂÃÄÅàáâãäå]/');
  //$aFind = array('/[ÀÁÂÃÄÅàáâãäå]/', '/[ÈÉÊËèéêë]/', '/[ÒÓÔÕÖØòóôõöø]/', '/[Çç]/', '/[ÌÍÎÏìíîï]/', '/[ÙÚÛÜùúûü]/', '/\s/', '/[^A-Za-z0-9\-]/', '/(^-)/', '/--+/', '/(-$)/');
  //$aFind = array('/à/', '/[éèê]/', '/ô/', '/ç/', '/ï/', '/[üû]/', '/\s/', '/[^A-Za-z0-9\-]/', '/(^-)/', '/--+/', '/(-$)/');
  //$aReplace = array('a', 'e', 'o', 'c', 'i', 'u', '-');
  
  // from http://ch2.php.net/manual/en/function.preg-replace.php#96586
  
  $aFind = array('À','Á','Â','Ã','Ä','Å','Æ','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ð','Ñ','Ò','Ó','Ô','Õ','Ö','Ø','Ù','Ú','Û','Ü','Ý','ß','à','á','â','ã','ä','å','æ','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','õ','ö','ø','ù','ú','û','ü','ý','ÿ','Ā','ā','Ă','ă','Ą','ą','Ć','ć','Ĉ','ĉ','Ċ','ċ','Č','č','Ď','ď','Đ','đ','Ē','ē','Ĕ','ĕ','Ė','ė','Ę','ę','Ě','ě','Ĝ','ĝ','Ğ','ğ','Ġ','ġ','Ģ','ģ','Ĥ','ĥ','Ħ','ħ','Ĩ','ĩ','Ī','ī','Ĭ','ĭ','Į','į','İ','ı','Ĳ','ĳ','Ĵ','ĵ','Ķ','ķ','Ĺ','ĺ','Ļ','ļ','Ľ','ľ','Ŀ','ŀ','Ł','ł','Ń','ń','Ņ','ņ','Ň','ň','ŉ','Ō','ō','Ŏ','ŏ','Ő','ő','Œ','œ','Ŕ','ŕ','Ŗ','ŗ','Ř','ř','Ś','ś','Ŝ','ŝ','Ş','ş','Š','š','Ţ','ţ','Ť','ť','Ŧ','ŧ','Ũ','ũ','Ū','ū','Ŭ','ŭ','Ů','ů','Ű','ű','Ų','ų','Ŵ','ŵ','Ŷ','ŷ','Ÿ','Ź','ź','Ż','ż','Ž','ž','ſ','ƒ','Ơ','ơ','Ư','ư','Ǎ','ǎ','Ǐ','ǐ','Ǒ','ǒ','Ǔ','ǔ','Ǖ','ǖ','Ǘ','ǘ','Ǚ','ǚ','Ǜ','ǜ','Ǻ','ǻ','Ǽ','ǽ','Ǿ','ǿ');
  
  $aReplace = array('A','A','A','A','A','A','AE','C','E','E','E','E','I','I','I','I','D','N','O','O','O','O','O','O','U','U','U','U','Y','s','a','a','a','a','a','a','ae','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','o','u','u','u','u','y','y','A','a','A','a','A','a','C','c','C','c','C','c','C','c','D','d','D','d','E','e','E','e','E','e','E','e','E','e','G','g','G','g','G','g','G','g','H','h','H','h','I','i','I','i','I','i','I','i','I','i','IJ','ij','J','j','K','k','L','l','L','l','L','l','L','l','l','l','N','n','N','n','N','n','n','O','o','O','o','O','o','OE','oe','R','r','R','r','R','r','S','s','S','s','S','s','S','s','T','t','T','t','T','t','U','u','U','u','U','u','U','u','U','u','U','u','W','w','Y','y','Y','Z','z','Z','z','Z','z','s','f','O','o','U','u','A','a','I','i','O','o','U','u','U','u','U','u','U','u','U','u','A','a','AE','ae','O','o');
  
  return strtolower(preg_replace(array('/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/'), array('', '-', ''), str_replace($aFind, $aReplace, $sValue)));
}

/**
 * Return a color between gray and xxx, depends on fValue
 */
function inter_color($fValue) {
  
  $iColor = 255 - intval(255 * $fValue);
  
  return "rgb($iColor, $iColor, 213)";
}

function float_format($mValue, $iDec = 2, $iPoint = '.', $iThousand = '\'') {
  
  return (is_float($mValue) ? number_format($mValue, $iDec, $iPoint, $iThousand) : $mValue);
}

/**
 * Check encoding and optionnaly return value in utf-8
 */
function checkEncoding($sContent) {
  
  if (Sylma::get('dom/encoding/check') && !mb_check_encoding($sContent, 'UTF-8')) {
    
    $sContent = utf8_encode($sContent); //t('EREUR D\' ENCODAGE'); TODO , result not always in utf-8
    dspm(xt('L\'encodage n\'est pas utf-8 %s', new HTML_Strong(stringResume($sContent))), 'xml/warning');
  }
  
  return $sContent;
}

/* Display function */

/*
 *
 **/
function dspf($mVar, $sStatut = SYLMA_MESSAGES_DEFAULT_STAT) {
  
  dspm(view($mVar, false), $sStatut); 
}

function dspm($mVar, $sStatut = SYLMA_MESSAGES_DEFAULT_STAT) {
  
  Controler::addMessage($mVar, $sStatut);
}

function dspl($sVar) {
  
  $fp = fopen(MAIN_DIRECTORY.Controler::getSettings('@path-config').'/debug.log', 'a+');
  fwrite($fp, "----\n".$sVar."\n"); //.Controler::getBacktrace()
  fclose($fp);
}

function view($mVar, $bFormat = false) {
  
  return Controler::formatResource($mVar, $bFormat);
}


