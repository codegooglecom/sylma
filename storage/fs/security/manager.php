<?php

namespace sylma\storage\fs\security;
use sylma\core, sylma\dom, sylma\storage\fs;

interface manager {

  const NS = 'http://www.sylma.org/storage/fs/security';

  public function __construct(fs\directory $dir);
  public function getPropagation();
  public function getDirectory();
  public function getFile($sName);
}