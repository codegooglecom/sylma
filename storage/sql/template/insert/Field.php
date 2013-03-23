<?php

namespace sylma\storage\sql\template\insert;
use sylma\core, sylma\storage\sql, sylma\schema\parser, sylma\parser\languages\common;

class Field extends sql\template\component\Field {

  const MSG_MISSING = 'The field %s is missing';

  public function reflectRead() {

    $this->launchException('Should not be used');
  }

  public function applyRegister() {

    $query = $this->getQuery();
    $window = $this->getWindow();
    $arguments = $window->getVariable('arguments');

    $var = $window->addVar($window->createCall($arguments, 'read'));

    //$content = $window->createCall($arguments, 'addMessage', 'php-bool', array(sprintf(self::MSG_MISSING, $this->getName())));
    //$test = $window->createCondition($window->createNot($var), $content);
    //$window->add($test);
//dsp('ok');
    $query->addSet($this, $var);

    //return $this->reflectSelf();
  }
}

