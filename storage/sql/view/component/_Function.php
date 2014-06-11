<?php

namespace sylma\storage\sql\view\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common, sylma\storage\sql;

class _Function extends Basic implements reflector\component, common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->allowText(true);
  }

  public function asArray() {

    $tree = $this->getParser()->getCurrentTree();
    $query = $this->checkQuery($tree->getQuery());

    $sName = $this->readx('@name');
    $sElement = $this->readx('@element');
    $sAlias = $this->readx('@alias');

    $element = $tree->getElement($sElement);

    $content = $this->getWindow()->toString(array($sName, '(', $element, ')', " AS `$sAlias`"));
    $query->setColumn($content, false, $sAlias);

    $this->log("SQL : function [$sName]");

    return array();
  }

  protected function checkQuery(sql\query\parser\Select $query) {

    return $query;
  }
}

