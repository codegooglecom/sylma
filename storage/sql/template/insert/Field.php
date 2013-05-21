<?php

namespace sylma\storage\sql\template\insert;
use sylma\core, sylma\storage\sql, sylma\schema\parser, sylma\parser\languages\common;

class Field extends sql\template\component\Field {

  const MSG_MISSING = 'The field %s is missing';

  public function reflectRegister() {

    $query = $this->getQuery();
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

  protected function reflectSelf() {

    //return null;
    $this->launchException('No self reflect');
  }
}

