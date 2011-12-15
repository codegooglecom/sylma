<?php

/**
 * Return the last value of an array
 * 
 * @param array $aArray
 * @param mixed $mDefault
 * 
 * @return mixed The last value or @param $mDefault
 */
function array_last(array $aArray, $mDefault = null) {
  
  if ($aArray) return array_val(count($aArray) - 1, $aArray);
  else return $mDefault;
}

/**
 * If key exists return the corresponding value, else return $mDefault
 * 
 * @param string $sKey The key to read
 * @param array $aArray The array to look into
 * @param mixed $mDefault The default value to return if key doesn't exists
 * 
 * @return mixed The key value or @param $mDefault
 */
function array_val($sKey, array $aArray, $mDefault = null) {
  
  //is_array($aArray) && (is_string($sKey) || is_numeric($sKey)) && 
  
  if (array_key_exists($sKey, $aArray)) return $aArray[$sKey];
  else return $mDefault;
}

/**
 * Merge arrays recursively, but instead of replaced by array similar keys are erased
 * 
 * @param array $array1 The source array, for wich values could be replaced
 * @param array $array2 The second array that will override first argument array
 * 
 * @return array A new array result of the combination
 * 
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


/**
 * Merge keys and values with a separation value
 *
 * @param string $sSep The separation string
 * @param array $aArray The array to fusion
 * 
 * @return array The new fusionned array
 */
function fusion($sSep, $aArray) {
  
  $aResult = array();
  
  foreach ($aArray as $sKey => $sVal) $aResult[] = $sKey.$sSep.$sVal;
  
  return $aResult;
}

/**
 * Implosion = @function fusion + @function implode
 *
 * @param string $sSepFusion The separation for fusion (keys + values)
 * @param string $sSepImplode The separation for implode (lines)
 * @param array $aArray The array to process
 *
 * @return string The new "implosed" array
 */
function implosion($sSepFusion, $sSepImplode, $aArray) {
  
  return implode($sSepImplode, fusion($sSepFusion, $aArray));
}

