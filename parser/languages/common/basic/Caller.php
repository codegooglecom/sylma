<?php

namespace sylma\parser\languages\common\basic;
use sylma\core, sylma\parser\languages\common;

class Caller implements common\argumentable {

  protected $closure;

  public function __construct(\Closure $closure) {

    $this->closure = $closure;
  }

  protected function parseArgumentable(common\argumentable $obj) {

    return $obj->asArgument();
  }

  public function asArgument() {

    $closure = $this->closure;
    return $this->parseArgumentable($closure());
  }
}

