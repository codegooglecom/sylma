<?php

namespace sylma\parser\reflector;
use \sylma\core, \sylma\storage\fs, \sylma\dom;

\Sylma::load('domed.php', __DIR__);

interface attributed extends domed {

  function parseAttributes(dom\node $el, dom\element $resultElement, $result);
  function onClose();
}

