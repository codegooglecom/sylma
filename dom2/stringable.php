<?php

namespace sylma\dom;

interface stringable {
  
  /**
   * @return string A DOM valid content
   */
  function asDOMString();
}