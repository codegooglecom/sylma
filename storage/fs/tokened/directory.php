<?php

namespace sylma\storage\fs\tokened;

interface directory {
  
  function registerToken($sName, $sValue, $bPropagate = false);
}