<?php

namespace sylma\storage\fs\security;
use \sylma\dom, \sylma\storage\fs;

interface updater implements manager {
  
  public function build();
  public function updateFileName($sName, $sNewName);
  public function updateFile($sName, $sOwner, $sGroup, $sMode);
  public function deleteFile($sName);
  public function updateDirectory($sOwner, $sGroup, $sMode);
}
