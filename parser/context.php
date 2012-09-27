<?php

namespace sylma\parser;

interface context {

  function add($mValue);
  function set($sPath, $mValue);
}
