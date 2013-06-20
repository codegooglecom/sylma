<?php

namespace sylma\view\test\grouped\samples;
use sylma\core, sylma\schema\cached\form;

class Form1 extends form\Form {

  public function addElement($sName, form\Type $element = null) {

    switch ($sName) {

      case 'email' :

        $element->setValue($element->getValue() . $this->read('add'));

        break;
    }

    return parent::addElement($sName, $element);
  }
}

