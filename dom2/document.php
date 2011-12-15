<?php

namespace sylma\dom;
//use \sylma\core;

require_once('node.php');
require_once('container.php');

interface document extends node, container {
  
  function isEmpty();
}