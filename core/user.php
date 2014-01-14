<?php

namespace sylma\core;
use sylma\core;

interface user {

  function __construct(core\user\Controler $controler, $sName = '', array $aGroups = array(), $bPrivate = false);
  
  function isPublic();
  function isPrivate();

  function load();
  function loadPublic();
}
