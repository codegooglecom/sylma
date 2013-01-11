<?php

namespace sylma\parser\action;
use sylma\parser, sylma\dom, sylma\storage\fs, sylma\parser\languages\common, sylma\parser\languages\php;

interface reflector extends parser\reflector\documented, parser\reflector\domed {

  function __construct(parser\action\Manager $controler, dom\handler $doc, fs\directory $dir);
  //function getInterface();
  //function setInterface(parser\caller $interface);
  function useTemplate();

  /**
   * @return array|php\basic\CallMethod|
   */
  //function runVar(common\_var $call, dom\collection $children);
  //function getNamespace($sPrefix = '');
  function getLastElement();
}