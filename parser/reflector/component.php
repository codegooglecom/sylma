<?php

namespace sylma\parser\reflector;
use sylma\core, sylma\dom, sylma\parser\reflector;

interface component {

  function __construct(reflector\domed $parent, dom\element $el, core\argument $arg = null, $bComponent = null, $bForeign = null, $bUnknown = null);
}

