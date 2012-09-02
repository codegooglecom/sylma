<?php

namespace sylma\storage\fs\basic\editable;
use \sylma\dom, \sylma\storage\fs;

require_once(dirname(__dir__) . '/tokened/Directory.php');
require_once(dirname(dirname(__dir__)) . '/editable/directory.php');

class Directory extends fs\basic\tokened\Directory implements fs\editable\directory {

  /**
   * Add or get a directory depends it exists or not
   *
   * @param type $sName
   * @return fs\directory
   */
  public function addDirectory($mPath) {

    $result = null;

    if (is_string($mPath) && $mPath) {

      if ($mPath{0} == '/') $mPath = substr($mPath, 1);
      $mPath = explode('/', $mPath);
    }

    if ($mPath) {

      $sName = array_shift($mPath);

      if (!$sub = $this->getDirectory($sName, self::DEBUG_NOT)) {

        $sub = $this->createDirectory($sName);
      }

      $result = $sub->addDirectory($mPath);
    }
    else {

      $result = $this;
    }

    return $result;
  }

  /**
   *
   * @param string $sName
   * @param boolean $bRandom
   * @return fs\file|null
   */
  public function createFile($sName, $bRandom = false) {

    if ($bRandom) $sName = 'tmp-' . uniqid() . '.' . $sName;

    return $this->getFile($sName, self::DEBUG_EXIST);
  }

  /**
   *
   * @param string $sName
   * @param boolean $bRandom
   * @return fs\directory|null
   */
  public function createDirectory($sName = '') {

    if (!$sName) $sName = 'tmp-' . uniqid();

    $dir = $this->getDirectory($sName, self::DEBUG_EXIST);
    $dir->save();

    return $dir;
  }

  public function save() {

    if ($this->doExist()) {

      $this->throwException('Cannot create, directory exists yet');
    }

    if (!$this->checkRights(\Sylma::MODE_WRITE)) {

      $this->throwException('You have no rights to create this directory');
    }

    if (!$bResult = mkdir($this->getRealPath(), 0711)) { //$this->getControler()->readArgument('system/rights')

      $this->throwException(sprintf('Cannot create directory called %s', $sName));
    }

    $this->doExist(true);
    //$result = $this->updateDirectory($sName);
  }

  public function updateRights($sOwner, $sGroup, $sMode) {

    if ($this->checkRightsArguments($sOwner, $sGroup, $sMode)) {

      return $this->getSettings()->updateDirectory($sOwner, $sGroup, $sMode);
    }

    return false;
  }

  public function rename($sNewName) {

    $result = null;

    if ($this->checkRights(\Sylma::MODE_WRITE)) {

      if (!rename($this->getRealPath(), $this->getParent()->getRealPath() . '/' . $sNewName)) {

        $this->throwException(t('Cannot rename file for unknown reason'));
      }

      $result = $this->getParent()->updateDirectory($sNewName);
    }

    return $result;
  }

  public function delete() {

    $bResult = false;

    if ($this->checkRights(\Sylma::MODE_WRITE)) {

      if ($this === $this->getControler()->getDirectory()) {

        $this->throwException(t('Cannot delete root directory !'));
      }

      $fs = $this->getControler('fs/cache');
      $tmp = $fs->getDirectory('#trash');

      $sName = 'trashed-' . uniqid() . '-' . $this->getName();

      $new = $tmp->getDirectory($sName, self::DEBUG_EXIST);
      $bResult = rename($this->getRealPath(), $new->getRealPath());

      $this->getParent()->updateDirectory($this->getName());
      $tmp->updateDirectory($sName);
    }

    return $bResult;
  }
}

