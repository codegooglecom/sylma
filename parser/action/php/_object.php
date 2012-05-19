<?php

namespace sylma\parser\action\php;

require_once('scope.php');

interface _object extends scope {
  function getInterface();
}