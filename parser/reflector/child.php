<?php

namespace sylma\parser\reflector;
use sylma\core;

/**
 * All domed interfaces (documented, elemented, attributed)
 */
interface child extends domed {

  function __construct(domed $parent, core\argument $arg = null);
}

