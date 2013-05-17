<?php

namespace sylma\schema\parser\component;
use sylma\core, sylma\schema, sylma\parser\reflector;

class Restriction extends reflector\component\Foreigner {

  protected $type;
  protected $base;

  protected $rules;

  public function setType(schema\parser\type $type) {

    $this->type = $type;
  }

  protected function getType() {

    return $this->type;
  }

  public function getBase() {

    return $this->base;
  }

  protected function setRules(core\argument $val) {

    if ($this->rules) {

      $this->rules->merge($val);
    }
    else {

      $this->rules = $val;
    }
  }

  public function getRule($sName) {

    return $this->getRules() ? $this->getRules()->read($sName) : null;
  }

  public function getRules() {

    return $this->rules;
  }

  public function getMaxLength() {


  }
}
