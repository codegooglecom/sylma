<?php

namespace sylma\parser\reflector;
use sylma\core, sylma\dom, sylma\parser\languages\common;

interface documented {

  /**
   * @return dom\element
   */
  function getCurrentElement();


  //function setWindow(common\_window $window);
  function getSourceDirectory($sPath = '');

  /**
   * Get a file relative to the source file's directory
   * @param string $sPath
   * @return fs\file
   */
  function getSourceFile($sPath = '');
  //function getReflector();

  /**
   * @return common\_window
   */
  function getWindow();
  //function build();
}

