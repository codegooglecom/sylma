<?php

namespace sylma\storage\sql\view\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common, sylma\storage\sql;

class _Function extends Basic implements reflector\component, common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->allowText(true);
    $this->allowForeign(TRUE);
  }

  public function asArray() {

    $aResult = array();

    $tree = $this->getParser()->getCurrentTree();
    $query = $this->checkQuery($tree->getQuery());

    $sName = $this->readx('@name');

    if (!$sElement = $this->readx('@element')) {

      $args = array();

      foreach ($this->parseChildren($this->getNode()->getChildren()) as $arg) {

        $args[] = $arg;
        $args[] = ', ';
      }

      array_pop($args);
    }
    else {

      $args = $tree->getElement($sElement);
    }

    $aContent = array($sName, '(', $args, ')');

    if (!$sAlias = $this->readx('@alias', false)) {

      $aResult = $aContent;
    }
    else {

      $aContent[] = " AS `$sAlias`";
      $query->setColumn($this->getWindow()->toString($aContent), false, $sAlias);
    }

    $this->log("SQL : function [$sName]");

    return $aResult;
  }

  protected function checkQuery(sql\query\parser\Select $query) {

    return $query;
  }
}

