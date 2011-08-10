<?php

namespace sylma\storage\fs\security;
use \sylma\dom, \sylma\storage\fs;

interface manager {
  
  public function __construct(fs\directory $directory);
  public function getPropagation();
  public function getDirectory();
  public function getFile($sName);
}