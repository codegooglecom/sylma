<?php

namespace sylma\schema\parser;

interface elemented {

  function getElement($sName, $sNamespace);
  function getElements();
}

