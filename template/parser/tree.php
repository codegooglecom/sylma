<?php

namespace sylma\template\parser;
use sylma\core, sylma\dom;

interface tree extends core\tokenable {

  //function reflectRead($sPath);
  function reflectApply($sMode);
}

