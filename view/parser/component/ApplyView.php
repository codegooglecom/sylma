<?php

namespace sylma\view\parser\component;
use sylma\core, sylma\dom, sylma\template\parser, sylma\parser\languages\common;

class ApplyView extends parser\component\Apply implements common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
  }

  public function asArray() {

    if ($sValue = $this->readx('@select')) {

      if ($sValue{0} === '$') {

        $this->throwException('Not yet implemented');
      }
    }

    if (!$sMode = $this->readx('@mode')) $sMode = '*';

    $result = $this->getTree()->reflectApply($sValue, $sMode);
//dsp($result);
    return array($result);
  }
}

