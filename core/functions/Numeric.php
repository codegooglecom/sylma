<?php

namespace sylma\core\functions\numeric;

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

function formatFloat($mValue, $iDec = 2, $iPoint = '.', $iThousand = '\'') {

  return (is_float($mValue) ? number_format($mValue, $iDec, $iPoint, $iThousand) : $mValue);
}

