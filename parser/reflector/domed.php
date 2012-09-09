<?php

namespace sylma\parser\reflector;
use \sylma\core, \sylma\storage\fs, \sylma\dom;

\Sylma::load('/core/controled.php');

interface domed extends core\controled {

  /**
   *
   * @param dom\element $el
   * @return type core\argumentable|array|null
   */
  function setParent(documented $parent);
}

