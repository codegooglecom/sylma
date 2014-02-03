<?php

namespace sylma\parser\reflector;
use \sylma\core, \sylma\storage\fs, \sylma\dom;

interface attributed extends domed {

  function parseAttributes(dom\element $el, $resultElement, $result);
}

