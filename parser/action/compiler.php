<?php

namespace sylma\parser\action;
use sylma\parser, sylma\dom, sylma\storage\fs, sylma\parser\action\php;

require_once('parser/domed.php');
require_once('dom2/domable.php');

interface compiler extends parser\domed, dom\domable {

  function __construct(parser\action\Controler $controler, dom\handler $doc, fs\directory $dir);
  function getInterface();
  function setInterface(parser\caller\Domed $interface);
  function useTemplate();
  function runCall(php\basic\CallMethod $call, dom\collection $children);

}