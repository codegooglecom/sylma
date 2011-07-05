<?php

function xt() {
  
  if (func_num_args()) {
    
    $aArguments = func_get_args();
    $sValue = array_shift($aArguments);
    
    if (count($aArguments) && Sylma::get('messages/format/enable')) return strtoxml(vsprintf(t($sValue), $aArguments));
    else return t($sValue);
  }
  
  return '';
}

function strtoxml($sValue, array $aNS = array(), $bMessage = false) {
  
  $mResult = null;
  $oDocument = new XML_Document;
  $sAttributes = '';
  
  if (!array_key_exists(0, $aNS)) $aNS[0] = SYLMA_NS_XHTML;
  
  foreach ($aNS as $sPrefix => $sUri) {
    
    if ($sPrefix) $sPrefix = 'xmlns:'.$sPrefix;
    else $sPrefix = 'xmlns';
    
    $sAttributes .= " $sPrefix=\"$sUri\"";
  }
  
  if ($oDocument->loadText('<div'.$sAttributes.'>'.$sValue.'</div>') &&
    $oDocument->getRoot() && !$oDocument->getRoot()->isEmpty()) {
    
    $mResult = $oDocument->getRoot()->getChildren();
    
  } else if ($bMessage) dspm(array(t('StrToXml : Transformation impossible'), new HTML_Tag('hr'), $sValue), 'xml/warning');
  
  return $mResult;
}


