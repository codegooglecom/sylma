<?php

namespace sylma\storage\sql\template\update;
use sylma\core, sylma\storage\sql, sylma\schema\parser, sylma\parser\languages\common;

class Table extends sql\template\insert\Table {

  protected $sMode = 'update';

  protected function loadQuery() {

    if (!$this->useElements()) {

      $result = null;
    }
    else {

      if ($this->isSub() and $id = $this->getElement('id', null, false)) {

        $window = $this->getWindow();
        $source = $this->getHandler()->call('getID');

        $update = $this->getQuery();
        $update->setWhere($id, '=', $source);

        $insert = $this->createQuery('insert');
        $insert->setHandler($this->getHandler());

        $delete = $this->createQuery('delete');
        $delete->setHandler($this->getHandler());
        $delete->setWhere($id, '=', $source);

        $result = $window->createSwitch($this->getHandler()->call('getMode'));

        $result->addCase('insert', $insert->getCall()->getInsert());
        $result->addCase('update', $update->getCall()->getInsert());
        $result->addCase('delete', $delete->getCall()->getInsert());
      }
      else {

        $result = parent::loadQuery();
      }
    }

    return $result;
  }

  protected function loadTriggers() {

    $aResult = parent::loadTriggers();

    if (!$this->isSub()) {

      $aResult[] = $this->getWindow()->toString('1', $this->getParser()->getView()->getResult());
    }

    return $aResult;
  }
}

