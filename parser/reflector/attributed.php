<?php

namespace sylma\parser\reflector;
use \sylma\core, \sylma\storage\fs, \sylma\dom;

interface attributed extends domed, child {

  function parseAttributes(dom\element $el, dom\element $resultElement, $result);
  function onClose(dom\element $el, dom\element $newElement);
}

