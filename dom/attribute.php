<?php

namespace sylma\dom;

require_once('node.php');
require_once('namespaced.php');

interface attribute extends node, namespaced {

  function getValue();
}