<?php

namespace sylma\parser;
use sylma\dom, sylma\storage\fs;

require_once('dom/domable.php');

interface action extends dom\domable {

  const NS = 'http://www.sylma.org/parser/action';
  const FILE_DEFAULT_MODE = \Sylma::MODE_READ;

  //const EXPORT_DIRECTORY = '#cache';

  function __construct(fs\file $file, array $aArguments = array());
}

