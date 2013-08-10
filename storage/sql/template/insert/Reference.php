<?php

namespace sylma\storage\sql\template\insert;
use sylma\core, sylma\storage\sql, sylma\parser\languages\common;

class Reference extends sql\template\component\Reference {

  protected function reflectID() {

    return $this->getParent()->getResult();
  }

  protected function reflectFunctionRef(array $aPath, $sMode, array $aArguments = array()) {

    $table = $this->getElementRef();
    $window = $this->getWindow();
    $parent = $this->getParent();

    $forms = $window->addVar($window->argToInstance(array()));
    $valid = $window->addVar($window->argToInstance(true));

    $item = $window->createVariable('', '\sylma\core\argument');
    $loop = $window->createLoop($this->getParent()->getElementArgument($this->getName(), 'get'), $item);

    $table->setSource($item);
    $table->loadHandler();

    $aContent[] = $window->toString($this->getParser()->parsePathToken($table, $aPath, $sMode, false, $aArguments));
    $aContent[] = $table->getValidation();
    $test = $window->createNot($table->callValidate());
    $aContent[] = $window->createCondition($test, $window->createAssign($valid, $window->argToInstance(false)));
    $aContent[] = $window->callFunction('array_push', 'php-boolean', array($forms, $table->getHandler()));

    $loop->setContent($aContent);
    $parent->addContent($loop);

    $item = $window->createVariable('', '\sylma\core\argument');
    $loop = $window->createLoop($forms, $item);

    $table->setHandler($item);
    $table->addElement($this->getForeign(), $this->reflectID());

    $loop->setContent(array($table->getExecution()));
    $parent->addValidate($valid);
    $parent->addTrigger(array($loop));
  }
}

