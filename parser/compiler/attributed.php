<?php

namespace sylma\parser\compiler;
use \sylma\core, \sylma\storage\fs, \sylma\dom;

require_once('domed.php');

interface attributed extends domed {

  function parseAttributes(dom\node $el, dom\element $resultElement, $result);
}

