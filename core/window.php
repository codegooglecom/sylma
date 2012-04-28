<?php

namespace sylma\core;
use sylma\core, sylma\storage\fs;

require_once('core/stringable.php');

interface window extends core\stringable {

  public function __construct(core\Initializer $controler, fs\file $file);
}
