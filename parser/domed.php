<?php

namespace sylma\parser;
use \sylma\core, \sylma\storage\fs, \sylma\dom;

require_once('core/controled.php');

interface domed extends core\controled {

  /**
   *
   * @param dom\element $el
   * @return type core\argumentable|array|null
   */
  function parse(dom\node $el);
  function setParent(domed $parent);
}

