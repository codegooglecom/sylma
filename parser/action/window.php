<?php

namespace sylma\parser\action;
use sylma\parser\languages\php;

interface window extends php\window, core\argumentable {

  function setContext($sName);
  function createVar(common\argumentable $val);
}

