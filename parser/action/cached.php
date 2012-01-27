<?php

namespace sylma\parser\action;
use sylma\core, sylma\dom, sylma\storage\fs;

require_once('dom2/domable.php');

interface cached extends dom\domable {

  function __construct(fs\directory $dir, core\factory $controler, core\argument $args);
}
