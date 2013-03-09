<?php

namespace sylma\schema\parser\type;
use sylma\core;

interface simple {

  function isComplex();
  function getName();
  function getNamespace();
}

