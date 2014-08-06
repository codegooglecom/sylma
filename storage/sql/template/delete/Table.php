<?php

namespace sylma\storage\sql\template\delete;
use sylma\core, sylma\storage\sql, sylma\parser\languages\common;

class Table extends sql\template\component\Table implements common\argumentable {

  protected $sMode = 'delete';

  public function parseRoot(\sylma\dom\element $el) {

    parent::parseRoot($el);
    $this->insertQuery(false);
  }

  public function asArgument() {

    //$content = $this->getWindow()->createGroup(array($this->getDummy()->call('asString')));
    $aResult[] = $this->getQuery();
    $aResult[] = $this->loadDummy();
    $content = array($this->getDummy()->call('asString'));
    $aResult[] = $this->getHandler()->getView()->addToResult($content, false);

    return $this->getWindow()->createGroup($aResult);
  }
}
