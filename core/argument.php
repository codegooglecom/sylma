<?php

namespace sylma\core;

interface argument {
  
  function get($sPath = '', $bDebug = true);
  function read($sPath = '', $bDebug = true);
  //function query($sPath = '', $bDebug = true);
  function set($sPath = '', $mValue = null);
  //function add($sPath = '', $mValue = null);
  function setParent(argument $parent);
  function getParent();
  // function merge();
  // function parse();
  // function __toString();
}

