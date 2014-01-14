<?php

namespace sylma\parser\languages\common;
use \sylma\core;

interface _var extends argumentable {

  function insert();
  function getName();

  /**
   * Used for closure declaration
   */
  function isStatic();
}