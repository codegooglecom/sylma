<?php

namespace sylma\schema\parser;
use sylma\core;

interface type {

  function isSimple();
  function isComplex();
  function getName();
  function getNamespace();
}

