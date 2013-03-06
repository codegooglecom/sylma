<?php

namespace sylma\template\parser;
use sylma\core, sylma\dom, sylma\parser\reflector;

class Texted extends reflector\basic\Component {

  protected function parseChildrenText(dom\text $node, array &$aResult) {

    $aResult[] = $node->getValue();
  }
}

