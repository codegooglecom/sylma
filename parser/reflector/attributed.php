<?php

namespace sylma\parser\reflector;
use \sylma\core, \sylma\storage\fs, \sylma\dom;

interface attributed extends domed {

  public function init();
  function parseAttributes(dom\element $el, $resultElement, $result);
  function onClose(dom\element $el, $newElement);
}

