<?php

namespace sylma\core\window;
use sylma\core, sylma\storage\fs;

interface file extends core\stringable {

  public function setFile(fs\file $file);
}
