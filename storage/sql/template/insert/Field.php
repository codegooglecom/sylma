<?php

namespace sylma\storage\sql\template\insert;
use sylma\core, sylma\storage\sql;

class Field extends sql\template\component\Field {

  const MSG_MISSING = 'The field %s is missing';

  public function reflectRegister($content = null, $sReflector = '', $sMode = '') {

    $this->setReflectorName($sReflector);
    $this->getParent()->addElement($this, $content, array(
      'optional' => $this->isOptional(),
      'default' => $this->getDefault(),
      'mode' => $sMode,
    ));
  }

  protected function reflectSelf($bHTML = false) {

    //return null;
    $this->launchException('No self reflect');
  }
}

