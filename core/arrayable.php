<?php

namespace sylma\core;

interface arrayable {

  /**
   * Allow exporting object as array.
   * 
   * @return array An array representing the object
   */
  function asArray();
}
