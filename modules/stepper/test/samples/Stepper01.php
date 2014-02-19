<?php

namespace sylma\modules\stepper\test\samples;
use sylma\core, sylma\modules\stepper;

class Stepper01 extends stepper\Browser {

  public function clearDirectory() {

    foreach ($this->getDirectory()->getFiles() as $file) {

      $file->delete();
    }
  }
}

