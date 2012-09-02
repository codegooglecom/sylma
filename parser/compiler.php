<?php

namespace sylma\parser;
use sylma\core, sylma\parser, sylma\storage\fs, sylma\dom, sylma\parser\languages\common;

require_once('core/module/Domed.php');

interface compiler {

  function getCache(fs\file $file);
  function build(fs\file $file, fs\directory $dir);
  function buildInto(fs\file $file, fs\directory $base, common\_window $window);
}