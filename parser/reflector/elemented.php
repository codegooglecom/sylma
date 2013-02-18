<?php

namespace sylma\parser\reflector;
use sylma\core, sylma\dom, sylma\parser\reflector;

interface elemented extends domed {

  function parseRoot(dom\element $el);
  function parseFromParent(dom\element $el);
  function parseFromChild(dom\element $el);
}

