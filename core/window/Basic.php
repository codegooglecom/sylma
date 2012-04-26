<?php

namespace sylma\core\window;
use sylma\core, sylma\storage\fs;

require_once('core/module/Controled.php');
require_once('core/window.php');

class Basic extends core\module\Controled implements core\window {

  protected $file;

  public function __construct(core\Initializer $controler, fs\file $file) {

    $this->setControler($controler);
    $this->setFile($file);

    $controler->setContentType($file->getExtension());
  }

  protected function setFile(fs\file $file) {

    $this->file = $file;
  }

  protected function getFile() {

    return $this->file;
  }

  public function asString() {

    return $this->getFile()->read();
  }
}
