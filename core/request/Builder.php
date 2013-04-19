<?php

namespace sylma\core\request;
use sylma\core, sylma\storage\fs;

class Builder extends Basic {

  public function __construct(fs\file $file) {

    $this->setFile($file);
  }

  public function asString() {

    $file = $this->getFile();

    return $file->getParent() . '/' . $file->getSimpleName();
  }
}

