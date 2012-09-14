<?php

namespace sylma\core\window;
use sylma\core, sylma\storage\fs;

require_once('core/stringable.php');

interface file extends core\stringable {

  public function setFile(fs\file $file);
}
