<?php

namespace sylma\schema\parser;
use sylma\core;

interface element extends container {

  function isComplex();

  function getName();
  function getNamespace();
}

