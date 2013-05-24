<?php

namespace sylma\storage\sql\template\insert;
use sylma\core, sylma\storage\sql, sylma\schema\parser, sylma\parser\languages\common;

class Foreign extends sql\template\component\Foreign {

  protected function reflectFunctionAll(array $aPath, $sMode) {

    return null;
  }

  protected function reflectFunctionRef(array $aPath, $sMode) {

    return null;
  }

  public function reflectRegister() {

    $query = $this->getParent()->getQuery();
    $window = $this->getWindow();
    $arguments = $window->getVariable('post');

    $type = $this->getType();
    $handler = $this->getParent()->getHandler();

    $sName = $this->getFormAlias();

    $val = $arguments->call('read', array($sName), 'php-string');
    $call = $handler->call('addElement', array($sName, $type->instanciate($val, array('alias' => $sName))));
    $window->add($call);

    //$content = $window->createCall($arguments, 'addMessage', 'php-bool', array(sprintf(self::MSG_MISSING, $this->getName())));
    //$test = $window->createCondition($window->createNot($var), $content);
    //$window->add($test);

    $query->addSet($this, $handler->call('readElement', array($sName)));

    //return $this->reflectSelf();
  }

  protected function _applyElement(sql\template\component\Table $element, array $aPath, $sMode) {

    return null;
  }
}

