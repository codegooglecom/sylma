<?php

namespace sylma\core\window\classes;
use sylma\core, sylma\storage\fs;

require_once('core/module/Controled.php');
require_once('core/window/file.php');

class File extends core\module\Controled implements core\window\file {

  protected $file;

  public function __construct(core\Initializer $controler) {

    $this->setControler($controler);
  }

  public function setFile(fs\file $file) {

    $this->file = $file;
  }

  protected function getFile() {

    return $this->file;
  }

  public function asString() {

    $init = $this->getControler();
    $file = $this->getFile();

    if (\Sylma::read('debug/enable')) $init->setHeaderCache(0);
    else $init->setHeaderCache(3600 * 24 * 365);

    $init->setHeaderContent($init->getMime($file->getExtension()));

    return $file->read();
  }
}
