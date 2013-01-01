<?php

namespace sylma\parser\reflector;
use sylma\core, sylma\dom, sylma\parser\languages\common;

interface documented extends dom\domable {

  /**
   *
   * @param dom\element $el
   * @return core\argumentable|array|null
   */
  //function build(common\_window $window);
  function setWindow(common\_window $window);
}

