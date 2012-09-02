<?php

namespace sylma\parser\action;
use sylma\parser, sylma\dom, sylma\storage\fs, sylma\parser\languages\common, sylma\parser\languages\php;

require_once('parser/compiler/domed.php');
require_once('dom/domable.php');

interface compiler extends parser\compiler\domed, dom\domable {

  function __construct(parser\action\Controler $controler, dom\handler $doc, fs\directory $dir);
  function getInterface();
  function setInterface(parser\caller $interface);
  function useTemplate();

  /**
   * @return array|php\basic\CallMethod|
   */
  function runVar(common\_var $call, dom\collection $children);

}