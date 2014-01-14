<?php

namespace sylma\storage\sql\view\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common, sylma\storage\sql;

class Trigger extends reflector\component\Foreigner implements reflector\component, common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->allowForeign(true);
  }

  protected function addToParser(sql\view\Resource $parser) {

    return $this->addToTree($parser->getTree());
  }

  protected function addToTree(sql\template\component\Rooted $tree) {

    return $tree->addTrigger(array($this->build()));
  }

  protected function build() {

    if ($this->getNode()->hasChildren()) {

      $aResult = $this->getWindow()->parseArrayables($this->parseChildren($this->getNode()->getChildren()));
      //$aResult = $this->parseChildren($this->getNode()->getChildren());
    }
    else {

      $aResult = array();
    }

    //return $this->getWindow()->createInstruction(current($aResult));
    return $aResult;
  }

  public function asArray() {

    if ($aContent = $this->addToParser($this->getParser())) {

      $result = array($this->getWindow()->createGroup($aContent));
    }
    else {

      $result = array();
    }

    return $result;
  }
}

