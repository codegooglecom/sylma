<?php

namespace sylma\storage\sql\view;
use sylma\core, sylma\dom, sylma\view\parser;

class Id extends parser\component\Id {

  public function parseRoot(dom\element $el) {

    $content = parent::parseRoot($el);
    $this->setContent($content);
  }

}

