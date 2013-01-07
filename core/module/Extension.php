<?php

namespace sylma\core\module;

require_once('Domed.php');

class Extension extends Domed {

  private $aDirectories = array();

  /**
   * Add a directory to the directories stack
   *
   * @param string $mDirectory The new directory
   */
  protected function setDirectory($mDirectory) {

    parent::setDirectory($mDirectory);
    array_unshift($this->aDirectories, parent::getDirectory());
  }

  /**
   * Get the last directory or a child of one of dir in the stack
   *
   * @return fs\directory First corresponding child dir in dirs stack, or last dir if path is empty
   */
  protected function getDirectory($sPath = '', $bDebug = true) {

    if (!$sPath) {

      $result = parent::getDirectory($sPath, $bDebug);
    }
    else {

      $result = null;
      $aDirs = $this->getDirectories();

      $fs = $this->getControler(static::FILE_MANAGER);

      foreach ($aDirs as $dir) {

        if ($result = $fs->getDirectory($sPath, $dir, false)) break;
      }

      if (!$result && $bDebug) {

        $this->throwException(sprintf('@directory %s does not exist', $result->getRealPath()));
      }
    }

    return $result;;
  }

  private function getDirectories() {

    return $this->aDirectories;
  }

  protected function getFile($sPath, $bDebug = true) {

    $file = null;
    $aDirs = $this->getDirectories();

    $fs = $this->getControler(static::FILE_MANAGER);

    foreach ($aDirs as $dir) {

      if ($file = $fs->getFile($sPath, $dir, false)) break;
    }

    if (!$file && $bDebug) {

      $this->throwException(sprintf('@file %s does not exist', $sPath));
    }

    return $file;
  }
/*
  protected function runAction($sPath, $aArguments = array()) {

    $aReverse = $this->getDirectories();

    foreach ($aReverse as $oDirectory) {

      $sRealPath = Controler::getAbsolutePath($sPath, $oDirectory);
      $oPath = new XML_Path($sRealPath, $aArguments, true, false, false);

      if ($oPath->getPath()) break;
    }

    if ($oPath->getPath()) return new XML_Action($oPath);
    return null;
  }
*/
}


