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

    //echo $fs->getDirectory($sResult);
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
