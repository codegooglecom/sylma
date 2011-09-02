<?php

namespace sylma\core\argument\test;
use \sylma\core, \sylma\storage\fs;

interface controler {
  
  /**
   * Set the current directory
   */
  function setDirectory($mDirectory);
  
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