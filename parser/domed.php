<?php

namespace sylma\parser;
use \sylma\core, \sylma\storage\fs, \sylma\dom;

require_once('core/controled.php');

interface domed extends core\controled {
  
  const NS = 'http://www.sylma.org/parser/action';
  
  function __construct(core\factory $controler, dom\handler $doc);
  function parseElement(dom\element $el);
}

