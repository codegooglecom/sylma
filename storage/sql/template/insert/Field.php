<?php

namespace sylma\storage\sql\template\insert;
use sylma\core, sylma\storage\sql, sylma\schema\parser, sylma\parser\languages\common;

class Field extends sql\template\component\Field {

  const MSG_MISSING = 'The field %s is missing';

  public function reflectRead() {

    $this->launchException('Should not be used');
  }

  public function reflectApply($sPath, $sMode = '') {

    if ($sPath) parent::reflectApply($sPath, $sMode);
    //$this->getSource()->insert();

    return null;
  }

  public function reflectRegister() {

    $query = $this->getQuery();
    $window = $this->getWindow();
    $arguments = $window->getVariable('post');

    $type = $this->getType();
    $arg = $arguments->call('read', array($this->getFormAlias()), 'php-string');
    $var = $type->escape($arg);

    //$content = $window->createCall($arguments, 'addMessage', 'php-bool', array(sprintf(self::MSG_MISSING, $this->getName())));
    //$test = $window->createCondition($window->createNot($var), $content);
    //$window->add($test);

    $query->addSet($this, $var);

    //return $this->reflectSelf();
  }

  protected function reflectSelf() {

    //return null;
    $this->launchException('No self reflect');
  }
}

