<?php

namespace sylma\core;
use sylma\core;

interface argument extends \Iterator {

  function get($sPath = '', $bDebug = true);
  function read($sPath = '', $bDebug = true);
  //function query($sPath = '', $bDebug = true);
  function set($sPath = '', $mValue = null);
  //function add($sPath = '', $mValue = null);
  function setParent(core\argument $parent);
  function getParent();

  /**
   * Replace object contained in argument with arrays with use of @method core\parsable::parse()
   */
  function normalize();
  // function merge();
  // function parse();
  // function __toString();
  function asArray();
}

