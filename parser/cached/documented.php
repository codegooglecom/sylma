<?php

namespace sylma\parser\cached;
use sylma\core, sylma\parser, sylma\dom;

interface documented {

  /**
   *
   * @param \sylma\dom\handler $doc
   * @return dom\handler
   */
  function parseDocument(dom\handler $doc);
}
