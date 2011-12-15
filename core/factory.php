<?php

namespace sylma\core;

interface factory {
  
  function create($sName, array $aArguments = array());
}