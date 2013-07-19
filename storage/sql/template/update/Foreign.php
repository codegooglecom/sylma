<?php

namespace sylma\storage\sql\template\update;
use sylma\core, sylma\storage\sql;

class Foreign extends sql\template\insert\Foreign {

  protected function loadID() {

    return $this->getParent()->getElementArgument('id');
  }

  protected function buildMultiple(sql\schema\table $junction, sql\schema\foreign $source, sql\schema\foreign $target) {

    $del = $this->loadSimpleComponent('template/delete');

    $del->setTable($junction);
    $del->setWhere($source, '=', $this->loadID());

    $aContent[] = $del;
    $aContent[] = parent::buildMultiple($junction, $source, $target);

    return $aContent;
  }
}

