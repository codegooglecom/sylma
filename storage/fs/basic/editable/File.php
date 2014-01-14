<?php

namespace sylma\storage\fs\basic\editable;
use \sylma\storage\fs;

class File extends fs\basic\File implements fs\editable\file {

  public function updateRights($sOwner, $sGroup, $sMode) {

    if ($this->checkRightsArguments($sOwner, $sGroup, $sMode)) {

      return $this->getSettings()->updateFile($this->getName(), $sOwner, $sGroup, $sMode);
    }

    return false;
  }

  public function move(fs\directory $dir, $sName = '') {

    return $this->moveSecured($dir, $sName);
  }

  public function moveFree($sDirectory, $sName = '') {

    return $this->moveSecured($sDirectory, $sName, false);
  }

  /**
   * Move a file with or without security rights, depends on @param $bSecured
   * - This file must be writable
   * - The target file shouldn't exist
   * - The target directory must be writable (see @param $bSecured)
   *
   * @param string $sDirectory Targeted directory
   * @param string $sName Optional new name
   * @param boolean $bSecured :
   * - If set to TRUE : Rights will be kept
   * - If set to FALSE :
   *   - Rights will not be kept and new rights will depends on new parent directory
   *   - The target directory must be readable, but not necessary writable
   * @return null|string|XML_File If $bSecured is set to true, the resulting new XML_file if move success or null if not
   *    If $bSecured is set to false, then it will return (string) path if move success or null if not.
   */
  protected function moveSecured(fs\directory $dir, $sNewName = '', $bSecured = true) {

    $result = null;

    if ($this->checkRights(\Sylma::MODE_WRITE)) {

      $sName = $this->getName();
      if (!$sNewName) $sNewName = $sName;

      if ($bSecured && !$dir->checkRights(\Sylma::MODE_WRITE)) {

        $this->throwException("Cannot write to directory : {$dir}");
      }

      if (rename($this->getRealPath(), $dir->getRealPath().'/'.$sNewName)) {

//        $this->update();
/*
        if ($dir != $this->getParent()) {

          if ($bSecured) {

            $dir->getSettings()->updateFile($sNewName, $this->getOwner(), $this->getGroup(), $this->getMode()); // copy security attributes
          }

          $this->getSettings()->deleteFile($sName);
        }
*/
        if ($bSecured) $result = $dir->updateFile($sNewName);
        else $result = $dir.'/'.$sNewName; // if not secured, target file may be not readable

        // update directory settings
        //$this->getSettings()->updateFileName($this->getName(), $sName);
      }
    }

    return $result;
  }

  public function rename($sNewName) {

    $result = null;

    if (!$this->checkRights(\Sylma::MODE_WRITE)) {

      $this->throwException('No write access');
    }

    if (rename($this->getRealPath(), $this->getParent()->getRealPath().'/'.$sNewName)) {

//        $this->update();
      $result = $this->getParent()->updateFile($sNewName);

      // update directory settings
      //$this->getSettings()->updateFileName($this->getName(), $sNewName);
    }

    return $result;
  }

  public function delete($bMessage = true, $bUpdateDirectory = true) {

    $bResult = null;

    if ($this->checkRights(\Sylma::MODE_WRITE)) {

      if ($bResult = unlink($this->getRealPath())) {

        //if ($bUpdateDirectory) $this->update();
        //$this->getSettings()->deleteFile($this->getName());
      }
    }

    return $bResult;
  }

  public function saveText($sContent) {

    $bResult = false;

    if (!$sContent) $this->throwException('Empty text not allowed as file\'s content');

    if (!$this->checkRights(\Sylma::MODE_WRITE)) {

      $this->throwException('You have not right to edit this file');
    }

    $bResult = file_put_contents($this->getRealPath(), $sContent);
    if (!$this->doExist()) chmod($this->getRealPath(), 0750);

    if (!$bResult) $this->throwException ('Cannot save text content for unknown reason');
    $this->bExist = true;

    //$this->update();

    return $bResult;
  }

/*  public function update() {

    $this->getParent()->updateFile($this->getName());
  }*/
}

