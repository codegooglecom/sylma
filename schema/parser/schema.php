<?php

namespace sylma\schema\parser;
use sylma\core;

interface schema {

  function getElement($sName);
  function getTargetNamespace();
}

