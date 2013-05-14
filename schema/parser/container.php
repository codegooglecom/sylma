<?php

namespace sylma\schema\parser;
use sylma\core;

interface container {

  /**
   * @return \sylma\schema\parser\element
   */
  function getElement($sName, $sNamespace);
  function getElements();
}

