<?php

namespace sylma\parser\languages\common;

require_once('scope.php');

interface _object extends scope {
  function getInterface();
}