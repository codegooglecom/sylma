<?php

namespace sylma\schema\parser;
use sylma\core, sylma\dom, sylma\schema\parser;

interface schema extends container {

  function getTargetNamespace();
  function parseName($sName, parser\namespaced $source = null, dom\element $context = null);
}

