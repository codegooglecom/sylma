<?php

namespace sylma\view\parser\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common, sylma\schema\template as ns_template;

class Template extends ns_template\Container {

  public function parseRoot(dom\element $el) {

    if (!$el->readx('@match', array(), false)) {

      $this->setMatch(self::MATCH_DEFAULT);
    }

    return parent::parseRoot($el);
  }

}

