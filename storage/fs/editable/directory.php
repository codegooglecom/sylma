<?php

namespace storage\fs\editable;

interface directory {
  /**
   * Add a file into this directory via XML_Document->freeSave()
   */
  public function addFreeDocument($sName, dom\document $oDocument);
  public function addDirectory($sName);
  
  /**
   * Change rights in corresponding SECURITY_FILE
   */
  public function updateRights($sOwner, $sGroup, $sMode);
  public function rename($sNewName);
  public function delete($bDeleteChildren = false);
}