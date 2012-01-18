<?php

namespace sylma\core\argument\test;
use \sylma\core, \sylma\storage\fs;

interface controler {
  
  /**
   * Give the current working directory
   */
  function getDirectory();
  
  /**
   * Set the tested argument object
   */
  function setArguments($mArguments = null, $bMerge = true);
  
  /**
   * Give the previous created argument object
   */
  function getArguments();
}