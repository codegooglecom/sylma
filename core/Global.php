<?php

function extractDirectory($sPath, $bObject = false) {
  
  $sPath = substr($sPath, strlen(getcwd().MAIN_DIRECTORY) + 1);
  if (SYLMA_XAMPP_BUG && isset($_ENV['OS']) && strpos($_ENV['OS'], 'Win') !== false) $sPath = str_replace('\\', '/', $sPath);
  else if (preg_match("/Win/", getenv("HTTP_USER_AGENT" ))) $sPath = str_replace('\\', '/', $sPath);
  //echo 'yo';
  //print_r($_ENV);
  $sResult = substr($sPath, 0, strlen($sPath) - strlen(strrchr($sPath, '/')));
  
  if ($bObject) return Controler::getDirectory($sResult);
  else return $sResult;
}

function pathWin2Unix($sPath) {
  
  return str_replace('\\', '/', $sPath);
}

/*** Array ***/

function array_last($aArray, $mDefault = null) {
  
  if ($aArray) return array_val(count($aArray) - 1, $aArray);
  else return $mDefault;
}

/**
 * Si il existe, renvoie la valeur de l'index du tableau , sinon renvoie la valeur de $mDefault
 */
function array_val($sKey, $aArray, $mDefault = null) {
  
  //is_array($aArray) && (is_string($sKey) || is_numeric($sKey)) && 
  
  if (array_key_exists($sKey, $aArray)) return $aArray[$sKey];
  else return $mDefault;
}

function array_clear($aArray, $sDefault = '') {
  
  $aCopyArray = $aArray;
  
  foreach ($aArray as $sKey => $sValue)if (!$sValue) unset($aCopyArray[$sKey]);
  
  return $aCopyArray;
}

function array_remove($sKey, &$aArray, $bDebug = true) {
  
  if ($bDebug || array_key_exists($sKey, $aArray)) {
    
    $mValue = $aArray[$sKey];
    unset($aArray[$sKey]);
    
  } else $mValue = null;
  
  return $mValue;
}

/**
 * Will merge array recurively, but instead of replaced by array, similar keys are erased
 * @param array $array1 The source array, for wich values could be replaced
 * @param array $array2 The second array that will override first argument array
 * @author andyidol at gmail dot com - http://www.php.net/manual/en/function.array-merge-recursive.php#102379
 * @author Rodolphe Gerber
 */
function array_merge_keys(array $array1, array $array2) {
  
  foreach($array2 as $key => $val) {
    
    if(array_key_exists($key, $array1) && is_array($val)) {
      
      $array1[$key] = array_merge_keys($array1[$key], $array2[$key]);
    }
    else {
      
      $array1[$key] = $val;
    }
  }

  return $array1;
}

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
 * Fusionne les clés et les valeurs en insérant une chaîne de séparation
 */
function fusion($sSep, $aArray) {
  
  $aResult = array();
  
  foreach ($aArray as $sKey => $sVal) $aResult[] = $sKey.$sSep.$sVal;
  
  return $aResult;
}

/**
 * Implosion = fusion + implode
 */
function implosion($sSepFusion, $sepImplode, $aArray) {
  
  return implode($sepImplode, fusion($sSepFusion, $aArray));
}

/**
 * Remove xml characters : & < >
 */
function remove_xml($sString) {
  
  return str_replace(array('&', '<', '>'), array(), $sString);
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
  
  if (Sylma::get('xml/encoding/check') && !mb_check_encoding($sContent, 'UTF-8')) {
    
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

/*
 * Pour le débuggage, affiche une variable dans un tag <pre> qui affiche les retours à la ligne
 **/
function dsp($mVar) {
  
  echo '<pre>';
  print_r($mVar);
  echo '</pre>';
}
