<?php

namespace sylma\schema\parser;
use sylma\core;

interface type extends namespaced {

  function isSimple();
  function isComplex();
  function getName();
}

