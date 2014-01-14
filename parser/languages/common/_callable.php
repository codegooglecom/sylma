<?php

namespace sylma\parser\languages\common;
use sylma\core;

interface _callable {

  public function call($sMethod, array $aArguments = array(), $mReturn = null, $bVar = false);
}

