<?php

namespace sylma\parser\compiler;
use \sylma\core, \sylma\storage\fs, \sylma\dom;

require_once('domed.php');

interface elemented extends domed {

  /**
   *
   * @param dom\element $el
   * @return core\argumentable|array|null
   */
  function parse(dom\node $source);
}
