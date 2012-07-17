<?php

namespace sylma\core\window;
use sylma\core, sylma\parser;

require_once('core/stringable.php');

interface action extends core\stringable {

  public function setAction(parser\action $action);
}
