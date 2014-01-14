<?php

namespace sylma\core\window;
use sylma\core, sylma\parser;

interface action extends core\stringable {

  public function setAction(parser\action\handler $action);
}
