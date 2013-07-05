<?php

namespace sylma\storage\sql\template\update;
use sylma\core, sylma\storage\sql;

class Foreign extends sql\template\insert\Foreign {

  protected function loadID() {

    return $this->reflectEscape($this->getParent()->getElementArgument('id'));
  }

  protected function buildMultiple(sql\schema\table $junction, sql\schema\field $source, sql\schema\field $target) {

    $del = $this->loadSimpleComponent('template/delete');

    $del->setTable($junction);
    $del->setWhere($source, '=', $this->loadID());

    $aContent[] = $del;
    $aContent[] = parent::buildMultiple($junction, $source, $target);

    return $aContent;
  }
}

