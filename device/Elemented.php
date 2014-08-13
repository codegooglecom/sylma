<?php

namespace sylma\device;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\view;

class Elemented extends reflector\handler\Elemented implements reflector\elemented
{

  public function parseContent(dom\collection $children) {

    $parser = $this->getParent();

    $content = $this->loadContent($parser, $children);
    $result = $parser->addToResult($content, false);

    return $result;
  }

  protected function loadContent(view\parser\Elemented $parser, dom\collection $children) {

    return $parser->getCurrentTemplate()->parseChildren($children);
  }
}
