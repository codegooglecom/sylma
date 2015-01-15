<?php

namespace sylma\schema\cached\view;
use sylma\core;

class Price extends Numeric {

   public static function format($sValue, array $aSettings) {

     $bSimple = isset($aSettings['simple']) && $aSettings['simple'];
     $sResult = $bSimple ? '' : \Sylma::read('schema/currency') . ' ';

     return $sResult . parent::format($sValue, $aSettings) . '.-';
   }
}

