<?php

namespace sylma\schema\template;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\template, sylma\parser\languages\common;

class Apply extends template\parser\component\Apply implements common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
  }

  public function asArray() {

    if ($sValue = $this->getNode()->readx('@select', array(), false)) {

      if ($sValue{0} === '$') {

        $this->throwException('Not yet implemented');
      }
    }

    $result = $this->getTree()->reflectApply($sValue);
//dsp($result);
    return array($result);
  }
}

