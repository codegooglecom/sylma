<?php

namespace sylma\storage\sql\template\insert;
use sylma\core, sylma\dom, sylma\storage\sql, sylma\parser\languages\common;

class Table extends sql\template\component\Table implements common\argumentable {

  protected $sMode = 'insert';
  protected $bInsertQuery = false;
  protected $bElements = false;

  protected $aContent = array();
  protected $aValidates = array();

  protected $parentDummy;

  public function parseRoot(dom\element $el) {

    parent::parseRoot($el);

    $this->setSource($this->getWindow()->getVariable('post'));
  }

  public function init($key = null, $parent = null) {

    if ($key) {

      $this->setKey($key);
    }

    $this->parentDummy = $parent;

    //$this->loadDummy($key, $parent);
  }

  public function addElement(sql\schema\element $el, $content = null, array $aArguments = array()) {

    if ($this->getDummy(false)) {

      $this->addElementToDummy($el, $content, $aArguments);
    }
    else {

      $this->getQuery()->addSet($el, $content);
    }

    $this->bElements = true;
  }

  protected function addElementToDummy(sql\schema\element $el, $content = null, array $aArguments = array()) {

    $sName = $el->getAlias();
    $handler = $this->getDummy(true);

    $aArguments = array_merge(array(
      'alias' => $sName,
      'title' => $el->getTitle(),
    ), $aArguments);

    array_filter($aArguments);

    if (is_null($content)) {

      $content = $this->getElementArgument($sName);
    }

    $call = $handler->call('addElement', array($sName, $el->buildReflector(array($content, $aArguments))));
    $this->addContent($call);


    //$content = $window->createCall($arguments, 'addMessage', 'php-bool', array(sprintf(self::MSG_MISSING, $this->getName())));
    //$test = $window->createCondition($window->createNot($var), $content);
    //$window->add($test);

    //$query->addSet($el, $handler->call('readElement', array($sName)));

  }

  public function getElementArgument($sName, $sMethod = 'read') {

    switch ($sMethod) {

      case 'read' : $sFormat = 'php-string'; break;
      case 'query' : $sFormat = 'php-array'; break;
      case 'get' : $sFormat = '\sylma\core\argument'; break;

      default :

        $this->launchException('Unknown format');
    }

    return $this->getSource()->call($sMethod, array($sName, false), $sFormat);
  }

  protected function loadDummy() {

    $key = $this->getKey();
    $parent = $this->parentDummy;

    $aArguments = $this->loadDummyArguments();
    $window = $this->getWindow();

    if ($this->isSub()) {

      $form = $this->buildReflector($aArguments, 'sub');
    }
    else {

      $form = $this->buildReflector($aArguments);
    }

    $var = $window->createVar($form);

    $this->setDummy($var);
    $this->addContent($var->getInsert());

    if ($this->isSub() && $this->getParent(false)) {

      $id = $this->getElement('id', null, false);
      $this->addContent($var->call('setSub', array($this->getParent()->getAlias(), $key, $parent, $id ? $id->getName() : null)));
    }
  }

  public function loadMultipleReference($sName, self $table, array $aPath, $sMode, array $aArguments = array(), sql\schema\element $foreign = null, $val = null) {

    $window = $this->getWindow();

    $item = $window->createVariable('', '\sylma\core\argument');
    $key = $window->createVariable('', 'php-integer');
    $loop1 = $window->createLoop($this->getElementArgument($sName, 'get'), $item, $key);

    $table->setSource($item);
    $table->init($key, $this->getDummy());

    $valid = $window->addVar($window->argToInstance(true));

    $aContent[] = $window->toString($this->getParser()->parsePathToken($table, $aPath, $sMode, false, $aArguments));
    $aContent[] = $table->getValidation();

    $test = $window->createNot($table->callValidate());
    $aContent[] = $window->createCondition($test, $window->createAssign($valid, $window->argToInstance(false)));

    $forms = $window->addVar($window->argToInstance(array()));

    $push = $window->callFunction('array_push', 'php-boolean', array($forms, $table->getDummy()));
    $aContent[] = $window->createCondition($table->getDummy()->call('isUsed'), $push);

    $loop1->setContent($aContent);
    $this->addContent($loop1);

    $item = $window->createVariable('', '\sylma\core\argument');
    $loop2 = $window->createLoop($forms, $item);

    $table->setDummy($item);

    if ($foreign) {

      $table->addElement($foreign, $val);
    }

    $loop2->setContent(array($table->getExecution()));

    $this->addValidate($valid);
    $this->addTrigger(array($loop2));
  }

  /*
   * WIP
  public function loadMultipleForeign(self $junction, sql\schema\element $source, sql\schema\element $target) {

    $window = $this->getWindow();
    $val = $window->createVariable('', 'php-null');
    $key = $window->createVariable('', 'php-integer');
    $loop = $window->createLoop($this->getElementArgument($this->getName(), 'get'), $val, $key);

    $junction->init($key, $this->getDummy());
    $junction->addElement($source, $this->getKey());

    $loop->addContent($junction->getValidation());

    $junction->addElement($target, $val);

    $this->addContent($loop);
    $this->addTrigger(array($junction));
  }
  */

  public function loadSingleReference($sName, sql\template\insert\Table $table, array $aPath, $sMode, array $aArguments = array(), sql\schema\element $foreign = null, $val = null) {

    $this->launchException('Single foreign insert as reference not implemented');
  }

  /**
   * @todo (wip) single reference insert, need main and sub form invert
   */
  public function _loadSingleReference($sName, self $table, array $aPath, $sMode, array $aArguments = array(), sql\schema\element $foreign = null, $val = null) {

    $window = $this->getWindow();

    $arg = $window->tokenToInstance(self::$sArgumentClass);
    $from = $window->callFunction('current', 'php-boolean', array($table->getElementArgument($sName, 'query')));
    $source = $window->createVar($window->createInstanciate($arg, array($from)));

    $table->setSource($source);
    $key = $window->addVar($window->argToInstance(0));
    $this->init();

    $table->init($key, $source);

    $aContent[] = $window->toString($this->getParser()->parsePathToken($table, $aPath, $sMode, false, $aArguments));
    $aContent[] = $this->getValidation();

    if ($foreign) {

      $table->addElement($foreign, $val);
    }

    $table->addContent($source->getInsert());
    $table->addContent($aContent);
    $table->addValidate($this->callValidate());
    $table->addTrigger(array($this->getExecution()));

    $this->addContent($table);

    //return $table;
  }

  public function getPosition() {

    return $this->getKey();
  }

  public function addTrigger(array $aContent) {

    $this->aTriggers[] = $aContent;
  }

  protected function useElements() {

    return $this->bElements;
  }

  protected function loadQuery() {

    if ($dummy = $this->getDummy(false)) {

      $this->getQuery()->setDummy($dummy);
    }

    $view = $this->getParser()->getView();

    $aResult[] = $this->getQuery();

    if ($this->isSub()) {

      //$result = $this->getQuery();
    }
    else {

      $aResult[] = $view->addToResult(array($this->getQuery()->getVar()), false, true);
    }

    return $aResult;
  }

  protected function loadTriggers() {

    $window = $this->getWindow();

    $aContent[] = $this->loadQuery();

    if ($aTriggers = $this->getTriggers()) {

      $aContent[] = $window->createGroup($aTriggers);
    }

    return $aContent;
  }

  protected function addContent($mVal) {

    $this->aContent[] = $mVal;
  }

  protected function addValidate($mVal) {

    if ($this->aValidates) {

      $this->aValidates[] = $this->getWindow()->createOperator('&&');
    }

    $this->aValidates[] = $mVal;
  }

  protected function callValidate() {

    $this->addValidate($this->getDummy(false)->call('validate'));

    return array_reverse($this->aValidates);
  }

  protected function getValidation() {

    $result = $this->getWindow()->createGroup($this->getContent());
    $this->aContent = array();

    return $result;
  }

  protected function getExecution() {

    $aResult = $this->aContent;
    $aResult[] = $this->loadTriggers();

    return $aResult;
  }

  protected function getContent() {

    $aResult[] = $this->aContent;

    return $aResult;
  }

  public function asArgument() {

    $window = $this->getWindow();

    // must be called first to fill content
    $aValidate = $window->createCondition($this->callValidate(), $this->loadTriggers());

    return $this->getWindow()->createGroup(array($this->getContent(), $aValidate));
  }
}

