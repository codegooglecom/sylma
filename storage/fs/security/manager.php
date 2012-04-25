<?php

namespace sylma\storage\fs\security;
use sylma\core, sylma\dom, sylma\storage\fs;

interface manager {

  public function __construct(fs\directory $directory);
  public function getPropagation();
  public function getDirectory();
  public function getFile($sName);
}