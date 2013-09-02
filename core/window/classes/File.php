<?php

namespace sylma\core\window\classes;
use sylma\core, sylma\storage\fs;

class File extends core\module\Managed implements core\window\file {

  protected $file;

  public function __construct(core\Initializer $manager) {

    $this->setManager($manager);
  }

  public function setFile(fs\file $file) {

    $this->file = $file;
  }

  protected function getFile() {

    return $this->file;
  }

  public function asString() {

    $init = $this->getManager();
    $file = $this->getFile();

    if (\Sylma::isAdmin()) {

      $init->setHeaderCache(0);
    }
    else {

      $init->setHeaderCache(3600 * 24 * 7);
    }

    $init->setHeaderContent($init->getMime($file->getExtension()));

    return $file->read();
  }
}
