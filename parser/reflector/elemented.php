<?php

namespace sylma\parser\reflector;
use sylma\core, sylma\dom, sylma\parser\reflector;

interface elemented extends domed {

  function parseRoot(dom\element $el);
  function parseFromParent(dom\element $el);
  function parseFromChild(dom\element $el);
  function parseComponent(dom\element $el);
  function loadComponent($sName, dom\element $el, $manager = null);
  function loadSimpleComponent($sName, $manager = null);
}

