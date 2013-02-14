<?php

namespace sylma\parser\reflector;
use sylma\core, sylma\dom, sylma\parser\languages\common;

interface documented extends dom\domable {

  function setWindow(common\_window $window);
  function getSourceDirectory();
}

