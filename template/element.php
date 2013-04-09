<?php

namespace sylma\template;

interface element {

  function setAttributes(array $aAttrs);
  function readAttribute($sName);
  function addToken($sToken, $sValue);
}
