<?php

namespace sylma\parser\reflector;
use \sylma\core, \sylma\storage\fs, \sylma\dom;

interface elemented extends domed {

  /**
   *
   * @param dom\element $el
   * @return core\argumentable|array|null
   */
  function parseRoot(dom\element $source);
}

