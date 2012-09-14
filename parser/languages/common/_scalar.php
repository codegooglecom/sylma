<?php

namespace sylma\parser\languages\common;

require_once('linable.php');

interface _scalar extends linable {

  function useFormat($sFormat);
}