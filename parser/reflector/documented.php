<?php

namespace sylma\parser\reflector;
use \sylma\core, \sylma\storage\fs, \sylma\dom;

interface documented {

  /**
   *
   * @param dom\element $el
   * @return core\argumentable|array|null
   */
  function parse(dom\node $source);
  function getNamespace($sPrefix = '');
  function getLastElement();
}

