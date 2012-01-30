<?php

namespace sylma\storage\fs\editable;

interface file {

  /**
   * Change rights in corresponding SECURITY_FILE
   */
  public function updateRights($sOwner, $sGroup, $sMode);

  /**
   * Move a file WITH security rights
   *
   * @param string $sDirectory Targeted directory
   * @param string $sName Optional new name
   * @return null|fs\file The resulting new file if move successed or NULL if not
   */
  public function move($sDirectory, $sName = '');

  /**
   * Move a file WITHOUT security rights
   *
   * @param string $sDirectory Targeted directory
   * @param string $sName Optional new name
   * @return null|string The file's path if move successed or NULL if not
   */
  public function moveFree($sDirectory, $sName = '');
  public function rename($sNewName);
  public function delete($bMessage = true, $bUpdateDirectory = true);
  public function saveText($sContent);

  /**
   * Call parent directory to reload (re-create) an XML_File reference, this one will be destroy
   */
  //public function update();
}
