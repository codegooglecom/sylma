<?php

namespace sylma\parser\reflector;
use sylma\core, sylma\dom, sylma\parser\reflector;

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
   * @return reflector\domed|null
   */
  function lookupParser($sNamespace);

  /**
   * @param string $sNamespace
   * @return reflector\domed|null
   */
  function createParser($sNamespace);

  /**
   * @return reflector\documented
   */
  function getRoot();

  function getNamespace();
  function getUsedNamespaces();
}

