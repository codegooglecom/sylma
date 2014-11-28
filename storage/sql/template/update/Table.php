<?php

namespace sylma\storage\sql\template\update;
use sylma\core, sylma\storage\sql, sylma\parser\languages\common;

class Table extends sql\template\insert\Table {

  protected $sMode = 'update';

  /**
   * @uses sql\query\parser\Basic::setHandler()
   * @uses sql\schema\component\Foreign::filterQuery()
   * @return array|common\argumentable
   */
  protected function loadQuery() {

    if (!$this->useElements()) {

      $result = null;
    }
    else {

      if ($this->isSub() and $id = $this->getElement('id', null, false)) {

        $result = $this->loadSub($id);
      }
      else {

        $result = parent::loadQuery();
      }
    }

    return $result;
  }

  protected function loadSub(sql\schema\field $id) {

    if ($dummy = $this->getDummy(false)) {

      $this->getQuery()->setDummy($dummy);
    }

    $window = $this->getWindow();
    $source = $this->getDummy()->call('getID');

    $update = $this->getQuery();
    $update->setWhere($id, '=', $source);
    $this->getParent()->filterQuery($update);

    $insert = $this->createQuery('insert');
    $insert->setDummy($this->getDummy());

    $delete = $this->createQuery('delete');
    $delete->setDummy($this->getDummy());
    $delete->setWhere($id, '=', $source);
    $this->getParent()->filterQuery($delete);

    $result = $window->createSwitch($this->getDummy()->call('getMode'));

    $result->addCase('insert', $insert->getCall()->getInsert());
    $result->addCase('update', $update->getCall()->getInsert());
    $result->addCase('delete', $delete->getCall()->getInsert());

    return $result;
  }

  public function loadSingleReference($sName, sql\template\insert\Table $table, array $aPath, $sMode, array $aArguments = array(), sql\schema\element $foreign = null, $val = null) {

    $window = $this->getWindow();

    //$table->isSub(true);

    //$arg = $window->tokenToInstance(self::$sArgumentClass);
    //$from = $window->callFunction('current', '\sylma\core\argument', array($this->getElementArgument($sName, 'get')));
    //$source = $window->createVar($window->createInstanciate($arg, array($from)));
    $source = $window->createVar($this->getElementArgument($sName, 'get'));
    //$source = $window->createVar($this->getElementArgument($sName, 'get'));

    $aContent[] = $source->getInsert();

    $table->setSource($source);
    $key = $window->addVar($window->argToInstance(0));
    $table->init($key, $this->getDummy());

    $aContent[] = $window->toString($this->getParser()->parsePathToken($table, $aPath, $sMode, false, $aArguments));
    $aContent[] = $table->getValidation();

    if ($foreign) {

      $this->addElement($foreign, $val);
    }

    $this->addContent($aContent);
    $this->addValidate($table->callValidate());
    $this->addTrigger(array($table->getExecution()));
  }

  protected function loadTriggers() {

    $aResult = parent::loadTriggers();

    if (!$this->isSub()) {

      $aResult[] = $this->getWindow()->toString('1', $this->getParser()->getView()->getResult());
    }

    return $aResult;
  }
}

