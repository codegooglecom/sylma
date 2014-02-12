<?php

namespace sylma\device;
use sylma\core, sylma\dom, sylma\parser\reflector;

class Elemented extends reflector\handler\Elemented implements reflector\elemented
{

  public function parseContent(dom\collection $children) {

    $parser = $this->getParent();

    $content = $parser->getCurrentTemplate()->parseChildren($children);
    $result = $parser->addToResult($content, false);

    return $result;
  }
}
