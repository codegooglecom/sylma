<?php

namespace sylma\storage\sql\template\hollow;
use sylma\core, sylma\storage\sql, sylma\schema\parser, sylma\parser\languages\common;

class Field extends sql\template\component\Field {

  public function reflectRead() {

    return null;
  }
}

