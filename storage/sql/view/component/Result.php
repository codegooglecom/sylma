<?php

namespace sylma\storage\sql\view\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common, sylma\storage\sql;

class Result extends reflector\component\Foreigner implements reflector\component, common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
  }

  protected function getFromParser(sql\view\Resource $parser) {

    return $this->getFromTree($parser->getTree());
  }

  protected function getFromTree(sql\template\component\Rooted $tree) {

    return $tree->getResult();
  }

  public function asArray() {

    return array($this->getFromParser($this->getParser()));
  }
}

