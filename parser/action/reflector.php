<?php

namespace sylma\parser\action;
use sylma\parser\reflector as reflector_ns, sylma\dom, sylma\storage\fs, sylma\parser\languages\common, sylma\parser\languages\php, sylma\parser\action;

interface reflector extends reflector_ns\documented {

  function __construct(action\Manager $controler, dom\handler $doc, fs\directory $dir);
  //function getInterface();
  //function setInterface(parser\caller $interface);
  function useTemplate();

  /**
   * @return array|php\basic\CallMethod|
   */
  //function runVar(common\_var $call, dom\collection $children);
  //function getNamespace($sPrefix = '');
}
