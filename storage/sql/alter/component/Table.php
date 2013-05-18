<?php

namespace sylma\storage\sql\alter\component;
use sylma\core, sylma\dom, sylma\storage\sql;

class Table extends sql\schema\component\Table {

  const SQL_PARSER = 'mysql';

  public function parseRoot(dom\element $el) {

    parent::parseRoot($el);
  }

  protected function loadColumns() {

    $sql = $this->getManager(self::SQL_PARSER);
    $cols = $sql->query("SHOW COLUMNS FROM `{$this->getName()}`", false);
    $aResult = array();

    foreach ($cols as $iPosition => $col) {

      $col->set('position', $iPosition);
      $aResult[$col->read('Field')] = $col;
    }

    $this->columns = $this->createArgument($aResult);
  }

  public function getColumn($sName) {

    return $this->columns->get($sName, false);
  }

  public function asUpdate() {

    $this->loadColumns();
    
    foreach ($this->getElements() as $element) {

      $aChildren[] = $element->asUpdate();
    }

    return "ALTER TABLE `{$this->getName()}` " . implode(",\n", $aChildren) . ';';
  }

  public function asCreate() {

    foreach ($this->getElements() as $element) {

      $aChildren[] = $element->asCreate();
    }

    return "CREATE TABLE IF NOT EXISTS `{$this->getName()}` (" . implode(",\n", $aChildren) . ');';
  }
}

