<?php

namespace sylma\parser\cached;
use sylma\core, sylma\dom;

/**
 * Domed, dynamic document parser
 * These parsers will be be used at the render time, to allow final DOM manipulations
 * As there is no cache, they must be performance friendly.
 */
interface documented {

  /**
   * @param $doc
   * @return dom\handler Render ready document
   */
  function parseDocument(dom\handler $doc);

  //function setParent(parser\action\cached $parent);
}
