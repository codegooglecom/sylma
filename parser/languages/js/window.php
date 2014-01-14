<?php

namespace sylma\parser\languages\js;
use sylma\parser\languages\common;

interface window extends common\_window {

  function createFunction(array $aArguments = array(), $sContent = '', $mReturn = null);
  function createObject(array $aProperties = array());
  function createGhost($sInterface);
  function createProperty($parent, $sName, $mReturn = null);
  function createCall($function, array $aArguments = array(), $mReturn = null);

}

