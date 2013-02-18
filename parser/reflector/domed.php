<?php

namespace sylma\parser\reflector;
use sylma\core, sylma\dom;

/**
 * All domed objects (elemented, attributed)
 */
interface domed {

  /**
   * @return dom\element
   */
  function getLastElement();

  /**
   * @param string $sNamespace
   * @return reflector\domed
   */
  function lookupParser($sNamespace);

  /**
   * @return reflector\documented
   */
  function getRoot();

  /**
   * @return common\_window
   */
  function getWindow();

  function getNamespace();
  function getUsedNamespaces();
}

