<?php

namespace sylma\parser\cached;
use sylma\core, sylma\parser, sylma\dom;

/**
 * Dynamic document parser
 * These parsers will be be used at the render time, to allow final DOM manipulations
 * As there is no cache, they must be performance friendly.
 */
interface documented {

  /**
   *
   * @param \sylma\dom\handler $doc
   * @return dom\handler
   */
  function parseDocument(dom\handler $doc);
  function setParent(parser\action\cached $parent);
}
