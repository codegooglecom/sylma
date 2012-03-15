<?php

namespace sylma\parser\action;
use sylma\parser, sylma\dom, sylma\storage\fs, sylma\parser\action\php;

require_once('parser/domed.php');
require_once('dom2/domable.php');

interface compiler extends parser\domed, dom\domable {

  function __construct(parser\action\Controler $controler, dom\handler $doc, fs\directory $dir);
  function getInterface();
  function setInterface(parser\caller $interface);
  function useTemplate();

  /**
   * @return array|php\basic\CallMethod|
   */
  function runVar(php\_var $call, dom\collection $children);

}