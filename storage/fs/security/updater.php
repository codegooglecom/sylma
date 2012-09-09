<?php

namespace sylma\storage\fs\security;
use \sylma\dom, \sylma\storage\fs;

interface updater implements manager {
  
  function build();
  function updateFileName($sName, $sNewName);
  function updateFile($sName, $sOwner, $sGroup, $sMode);
  function deleteFile($sName);
  function updateDirectory($sOwner, $sGroup, $sMode);
}
