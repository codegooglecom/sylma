<?php

namespace sylma\schema\cached\view;
use sylma\core;

class Price extends Numeric {

   public static function format($sValue, array $aSettings) {

     return \Sylma::read('schema/currency') . ' ' . parent::format($sValue, $aSettings) . '.-';
   }
}

