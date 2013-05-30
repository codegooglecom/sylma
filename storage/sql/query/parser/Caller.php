<?php

namespace sylma\storage\sql\query\parser;
use sylma\core, sylma\parser\languages\common;

class Caller implements common\argumentable {

  protected $query;

  public function __construct(Basic $query) {

    $this->query = $query;
  }

  public function asArgument() {

    return $this->query->getString()->asArgument();
  }
}

