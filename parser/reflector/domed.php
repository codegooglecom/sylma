<?php

namespace sylma\parser\reflector;
use sylma\core, sylma\dom;

/**
 * All domed interfaces (documented, elemented, attributed)
 */
interface domed extends core\controled {

  //function __construct(domed $parent, core\argument $arg = null);
/**
   *
   * @param dom\element $el
   * @return core\argumentable|array|null
   */
  function parseRoot(dom\element $source);

  //function useNamespace($sNamespace, $bParent = false);
  function getParser($sNamespace);

  /**
   *
   * @param dom\element $el
   * @return type core\argumentable|array|null
   */
  //function setParent(documented $parent);
}

