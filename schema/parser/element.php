<?php

namespace sylma\schema\parser;
use sylma\core;

interface element {

  function isComplex();

  /**
   * @return \sylma\schema\parser\element
   */
  function getElement($sName, $sNamespace);
  function getName();
  function getNamespace();
}

