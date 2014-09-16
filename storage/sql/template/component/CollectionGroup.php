<?php

namespace sylma\storage\sql\template\component;
use sylma\core, sylma\storage\sql, sylma\template;

class CollectionGroup extends Collection
{

  protected $bGrouped = false;
  protected $groupResult = null;

  public function reflectApplyFunction($sName, array $aPath, $sMode, $bRead = false, $sArguments = '', array $aArguments = array()) {

    switch ($sName) {

      case 'group-value' : $result = $this->groupValue; break;
      case 'group' : $result = $this->reflectFunctionGroups($sMode, $aArguments); break;
      default :

        $result = parent::reflectApplyFunction($sName, $aPath, $sMode, $bRead, $sArguments, $aArguments);
    }

    return $result;
  }

  protected function reflectFunctionGroups($sMode, array $aArguments = array()) {

    $window = $this->getWindow();
    $return = $this->getHandler()->getView()->getResult();
    $query = $this->getQuery()->getVar();

    $true = $window->argToInstance(true);
    $empty = $window->argToInstance('');
    $array = $window->argToInstance(array());

    $aResult[] = $this->prepareApply();

    $subs = $window->createVar($array);
    $aResult[] = $subs->getInsert();

    $current = $window->createVar($empty);

    $prev = $window->createVar($empty);
    $aResult[] = $prev->getInsert();

    $this->groupResult = $subs;
    $this->groupValue = $prev;
    $this->bGrouped = true;

    //if ($this->getQuery()->getColumns()) {

      $continue = $window->createVar($true);
      $aResult[] = $continue->getInsert();

      $loop = $window->createWhile($continue);

      $loop->addContent($this->getSource()->getInsert($query->call('current')));
      $loop->addContent($this->getKey()->getInsert($query->call('key')));

      $source = $this->getSource();
      $key = $this->getKey();

      $loop->addContent($current->getInsert($empty));
      $loop->addContent($window->createCondition($source, $window->toString($aArguments, $current)));

      $isHead = $window->createCondition($window->createExpression(array(
        $window->createNot($this->getSource()), '||', $key, '&&', $prev, '!=', $current,
      )));

      $first = $window->createVar($window->callFunction('current', '\sylma\core\argument', array($subs)));
      $this->setSource($first);

      $content = $window->toString($this->reflectApply($sMode, $aArguments), $return);

      $this->setSource($source);

      $isHead->addContent(array(
        $first->getInsert(),
        $content,
        $subs->getInsert($array),
      ));

      $loop->addContent($isHead);

      $isLast = $window->createCondition($this->getSource());

      $isLast->addContent($window->createAssign($prev, $current));
      $isLast->addContent($window->callFunction('array_push', 'php-boolean', array($subs, $this->getSource())));
      $isLast->addContent($query->call('next'));
      $isLast->addElse($window->createAssign($continue, $window->argToInstance(false)));

      $loop->addContent($isLast);

      $aResult[] = $loop;
    //}

    return $aResult;
  }

  public function reflectApplyAll($sMode, array $aArguments = array()) {

    if ($this->bGrouped) {

      $this->bGrouped = false;
      $table = $this->getTable();
      $window = $this->getWindow();

      $source = $this->getSource();
      $key = $this->getKey();

      $this->setSource($window->createVariable('', '\sylma\core\argument'));
      $this->setKey($window->createVariable('', 'php-integer'));

      $table->setSource($this->getSource());
      $table->setKey($this->getKey());

      $result = $window->createLoop($this->groupResult, $this->getSource(), $this->getKey());
      $content = $window->parse($this->getTable()->reflectApply($sMode), false);

      $result->setContent($content);

      $this->setSource($source);
      $this->setKey($key);
    }
    else {

      $result = parent::reflectApplyAll($sMode, $aArguments);
    }

    return $result;
  }
}
