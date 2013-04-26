<?php

namespace sylma\parser\languages\common;
use sylma\core;

interface callable {

  public function call($sMethod, array $aArguments = array(), $mReturn = null, $bVar = false);
}

