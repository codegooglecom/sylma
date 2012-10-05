<?php

namespace sylma\core;
use sylma\core;

interface argument extends \Iterator {

  const NORMALIZE_EMPTY_ARRAY = 1;
  const NORMALIZE_ARGUMENT = 2;

  const NORMALIZE_DEFAULT = 3;

  function get($sPath = '', $bDebug = true);
  function read($sPath = '', $bDebug = true);
  //function query($sPath = '', $bDebug = true);
  function set($sPath = '', $mValue = null);
  function add($mValue);
  //function shift($mValue);
  function setParent(core\argument $parent);
  function getParent();

  /**
   * Replace object contained in argument with arrays with use of @method core\parsable::parse()
   */
  function normalize($iMode = self::NORMALIZE_DEFAULT);
  // function merge();
  // function parse();
  // function __toString();
  function asArray($bEmpty = false);
}

