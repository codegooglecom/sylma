<?php

namespace sylma\parser\reflector;
use sylma\core, sylma\dom, sylma\parser\languages\common;

interface documented extends dom\domable {

  /**
   *
   */
  function getWindow();
  function setWindow(common\_window $window);

  /**
   * Allow child parser to re-send element to parent
   *
   * @param $node
   * @return common\argumentable
   */
  function parse(dom\node $node);
}

