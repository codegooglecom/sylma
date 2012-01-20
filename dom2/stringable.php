<?php

namespace sylma\dom;

interface stringable {

  /**
   * @return string A DOM valid content
   */
  function asString($bFormat = false);
}