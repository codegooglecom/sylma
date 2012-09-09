<?php

namespace sylma\parser\reflector;
use \sylma\core, \sylma\storage\fs, \sylma\dom;

\Sylma::load('domed.php', __DIR__);

interface elemented extends domed {

  /**
   *
   * @param dom\element $el
   * @return core\argumentable|array|null
   */
  function parseRoot(dom\element $source);
}

