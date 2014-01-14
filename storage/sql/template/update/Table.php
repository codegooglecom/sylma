<?php

namespace sylma\storage\sql\template\update;
use sylma\core, sylma\storage\sql, sylma\parser\languages\common;

class Table extends sql\template\insert\Table {

  protected $sMode = 'update';

  /**
   * @uses sql\query\parser\Basic::setHandler()
   * @uses Reference::secureQuery()
   * @return array|common\argumentable
   */
  protected function loadQuery() {

    if (!$this->useElements()) {

      $result = null;
    }
    else {

      if ($this->isSub() and $id = $this->getElement('id', null, false)) {

        $window = $this->getWindow();
        $source = $this->getDummy()->call('getID');

        $update = $this->getQuery();
        $update->setWhere($id, '=', $source);
        $this->getParent()->secureQuery($update);

        $insert = $this->createQuery('insert');
        $insert->setHandler($this->getDummy());

        $delete = $this->createQuery('delete');
        $delete->setHandler($this->getDummy());
        $delete->setWhere($id, '=', $source);
        $this->getParent()->secureQuery($delete);

        $result = $window->createSwitch($this->getDummy()->call('getMode'));

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

