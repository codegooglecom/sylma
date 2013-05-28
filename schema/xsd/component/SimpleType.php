<?php

namespace sylma\schema\xsd\component;
use sylma\core, sylma\dom, sylma\schema\parser, sylma\parser\reflector;

class SimpleType extends parser\component\Simple {

  public function parseRoot(dom\element $el) {

    $el = $this->setNode($el);

    $this->setName($this->readx('@name'));
  }

  protected function parseDefine() {

    $iCount = $this->getNode(false) ? $this->getNode()->countChildren() : 0;

    if ($iCount) {

      if ($iCount > 1) {

        $this->launchException("Bad child count, only one expected", get_defined_vars());
      }

      $result = $this->parseComponentRoot($this->getNode());
    }
    else {

      $result = false;
    }

    return $result;
  }

  public function getDefine() {

    if (is_null($this->define)) {

      if ($define = $this->parseDefine()) $this->setDefine($define);
      else $this->define = false;
    }

    return parent::getDefine();
  }

}
