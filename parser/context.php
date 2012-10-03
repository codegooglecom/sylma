<?php

namespace sylma\parser;

interface context {

  function shift($mValue);
  function add($mValue);
  function set($sPath, $mValue);
}
