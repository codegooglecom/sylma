<?php

namespace sylma\schema\template;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\template, sylma\parser\languages\common;

class Apply extends template\parser\component\Apply implements common\arrayable {

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

