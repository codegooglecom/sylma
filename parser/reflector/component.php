<?php

namespace sylma\parser\reflector;
use sylma\core, sylma\dom, sylma\parser\reflector;

interface component {

  function __construct(reflector\domed $parser, core\argument $arg = null);
  function parseRoot(dom\element $el);
}

