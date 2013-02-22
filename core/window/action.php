<?php

namespace sylma\core\window;
use sylma\core, sylma\parser\action;

require_once('core/stringable.php');

interface action extends core\stringable {

  public function setAction(action\handler $action);
}
