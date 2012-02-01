<?php

namespace sylma\parser\action\php;

require_once('linable.php');

interface _scalar extends linable {

  function useFormat($sFormat);
}