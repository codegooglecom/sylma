<?php

namespace sylma\parser\action;
use \sylma\core, \sylma\storage\fs, \sylma\dom;

class cached {
  
  function __construct(fs\directory $dir, core\factory $controler);
  
  /**
   * @return array|dom\node|dom\domable
   */
  function parseAction();
}

