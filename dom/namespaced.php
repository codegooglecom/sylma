<?php

namespace sylma\dom;
use \sylma\dom;

interface namespaced {

  /**
   * @param boolean $bLocal If TRUE, return the local name, if FALSE return the full name (with prefix)
   * @return string The name of the element
   */
  function getName($bLocal = true);

  /**
   * @return string the namespace of the element or empty string
   */
  function getNamespace();
  function getPrefix();
}

?>
