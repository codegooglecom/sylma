<?php

namespace sylma\core\window\classes;
use sylma\core, sylma\storage\fs;

class File extends core\module\Filed implements core\window\file {

  public function __construct(core\Initializer $manager, core\argument $args = null) {

    $this->setManager($manager);
    $this->setSettings($args);
  }

  public function setFile(fs\file $file) {

    return parent::setFile($file);
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
