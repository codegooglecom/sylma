<?php

namespace sylma\storage\fs\editable;
use sylma\dom;

interface directory {

  function addDirectory($sName);
  function createDirectory($sName = '');
  function createFile($sName, $bRandom = false);
  
  /**
   * Change rights in corresponding SECURITY_FILE
   */
  function updateRights($sOwner, $sGroup, $sMode);
  function rename($sNewName);
  function delete();
}