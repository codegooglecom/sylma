<?php

namespace sylma\storage\fs\basic\editable;

use \sylma\storage\fs;

require_once(dirname(__dir__) . '/File.php');
require_once(dirname(dirname(__dir__)) . '/editable/file.php');

class File extends fs\basic\File implements fs\editable\file {

  public function updateRights($sOwner, $sGroup, $sMode) {

    if ($this->checkRightsArguments($sOwner, $sGroup, $sMode)) {

      return $this->getSettings()->updateFile($this->getName(), $sOwner, $sGroup, $sMode);
    }

    return false;
  }

  public function move($sDirectory, $sName = '') {

    return $this->moveSecured($sDirectory, $sName);
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
  protected function moveSecured($sDirectory, $sNewName = '', $bSecured = true) {

    $oResult = null;

    if ($this->checkRights(MODE_WRITE)) {

      $sName = $this->getName();
      if (!$sNewName) $sNewName = $sName;

      if ((!$oDirectory = $this->getControler()->getDirectory($sDirectory)) ||
        ($bSecured && !$oDirectory->checkRights(MODE_WRITE))) {

        dspm(xt('Impossible de déplacer %s dans %s, le répertoire est introuvable ou privé',
          $this->parse(), new HTML_Strong($sDirectory)), 'warning');
      }
      else if (rename($this->getRealPath(), $oDirectory->getRealPath().'/'.$sNewName)) {

//        $this->update();

        if ($oDirectory != $this->getParent()) {

          if ($bSecured) $oDirectory->getSettings()->updateFile($sNewName,
            $this->getOwner(), $this->getGroup(), $this->getMode()); // copy security attributes

          $this->getSettings()->deleteFile($sName);
        }

        if ($bSecured) $oResult = $oDirectory->updateFile($sNewName);
        else $oResult = $oDirectory.'/'.$sNewName; // if not secured, target file may be not readable

        // update directory settings
        $this->getSettings()->updateFileName($this->getName(), $sName);

      } else dspm(t('Impossible de déplacer le fichier !'), 'warning');
    }

    return $oResult;
  }

  public function rename($sNewName) {

    $oResult = null;

    if ($this->checkRights(MODE_WRITE)) {

      if (rename($this->getRealPath(), $this->getParent()->getRealPath().'/'.$sNewName)) {

//        $this->update();
        $oResult = $this->getParent()->updateFile($sNewName);

        \Controler::addMessage(t('Fichier renommé !'), 'success');

        // update directory settings
        $this->getSettings()->updateFileName($this->getName(), $sNewName);

      } else \Controler::addMessage(t('Impossible de renommer le fichier !'), 'warning');
    }

    return $oResult;
  }

  public function delete($bMessage = true, $bUpdateDirectory = true) {

    $bResult = null;

    if ($this->checkRights(MODE_WRITE)) {

      if ($bResult = unlink($this->getSystemPath())) {

//        if ($bUpdateDirectory) $this->update();

        // update directory settings
        $this->getSettings()->deleteFile($this->getName());

        if ($bMessage) dspm(xt('Suppression du fichier %s', $this->parse()), 'file/notice');
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

    if (!$bResult) $this->throwException ('Cannot save text content for unknown reason');

    //$this->update();

    return $bResult;
  }

/*  public function update() {

    $this->getParent()->updateFile($this->getName());
  }*/
}

