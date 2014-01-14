<?php

namespace sylma\view\test\grouped\samples;
use sylma\core, sylma\schema\cached\form;

class String1 extends form\_String {

  public function getValue() {

    return 'override1';
  }
}

