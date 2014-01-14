<?php

namespace sylma\dom;

interface domable {

  /**
   * @return dom\handler|dom\collection
   */
  function asDOM();
}

?>
