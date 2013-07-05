<?php

namespace sylma\view\parser\component;
use sylma\core, sylma\dom;

class Traverser extends Container {

  protected function loadElementUnknown(dom\element $el) {

    $aContent = $this->parseChildren($el->getChildren());
    
    return $aContent;
  }

  protected function parseChildrenText(dom\text $node, array &$aResult) {

    $aResult[] = $node->getValue();
  }

  public function asArgument() {

    return $this->asArray();
  }
}

