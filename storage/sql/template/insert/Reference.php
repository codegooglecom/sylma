<?php

namespace sylma\storage\sql\template\insert;
use sylma\core, sylma\storage\sql, sylma\parser\languages\common;

class Reference extends sql\template\component\Reference {

  public function reflectRegister() {

    $el = $this->getElementRef();
    $el->isRoot(true);
    $result = $el->reflectApply();
    
    $this->getParent()->addTrigger($result);
  }
}

