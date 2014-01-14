<?php

namespace sylma\dom;
use \sylma\dom;

interface container {
  
  function setRoot(dom\element $el);
  
  /**
   * @return dom\element The root aka first element of the document
   */
  function getRoot();
}

?>
