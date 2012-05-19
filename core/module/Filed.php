<?php

namespace sylma\core\module;
use \sylma\core, \sylma\storage\fs;

require_once('core/argument/Filed.php');
require_once('core/functions/Path.php');
require_once('Argumented.php');

abstract class Filed extends Argumented {

  const FS_CONTROLER = 'fs';

  protected $directory = null;

  protected static $sArgumentClass = 'sylma\core\argument\Filed';
  protected static $sArgumentFile = 'core/argument/Filed.php';

  protected function createArgument($mArguments, $sNamespace = '') {

    $result = null;

    if (is_string($mArguments)) {

      $result = $this->createArgumentFromString($mArguments, $sNamespace);
    }
    else {

      $result = parent::createArgument($mArguments, $sNamespace);
    }

    return $result;
  }

  private function createArgumentFromString($sPath, $sNamespace) {

    $file = $this->getFile($sPath);
    $result = parent::createArgument((string) $file, $sNamespace);

    return $result;
  }

  protected function setArguments($mArguments = null, $bMerge = true) {

    if ($mArguments !== null) {

      if (is_string($mArguments)) {

        $mArguments = $this->createArgumentFromString($mArguments, $this->getNamespace());
      }

      parent::setArguments($mArguments);
    }
    else {

      $this->arguments = null;
    }

    return $this->getArguments();
  }

  /**
   * Allow relative paths for classe's files
   *
   * @param type $sName
   * @param array $aArguments
   * @param type $sDirectory
   * @return mixed
   */
  public function create($sName, array $aArguments = array(), $sDirectory = '') {

    if (!$sDirectory) $sDirectory = $this->getDirectory('', false);

    return parent::create($sName, $aArguments, $sDirectory);
  }

  /**
   * Set the current directory
   * @param fs\directory|string $mPath An object or string to set as default directory
   */
  protected function setDirectory($mDirectory) {

    if (is_string($mDirectory)) {

      $fs = $this->getControler('fs');
      $this->directory = $fs->extractDirectory($mDirectory);
    }
    else {

      $this->directory = $mDirectory;
    }

    // check if directory is accessible
    $this->getDirectory();
  }

  protected function loadControler($sName) {

    $result = null;

    if ($sName == 'fs') {

      $result = \Sylma::getControler(static::FS_CONTROLER);
    }
    else {

      $result = parent::loadControler($sName);
    }

    return $result;
  }

  /**
   * @return fs\directory The current directory
   */
  protected function getDirectory($sPath = '', $bDebug = true) {

    if ($sPath) {

      $dir = $this->getDirectory();

      $result = $dir->getControler()->getDirectory($sPath, $dir, $bDebug);
    }
    else {

      $result = $this->directory;
    }

    if (!$result && $bDebug) {

      $this->throwException('No base directory defined');
    }

    return $result;
  }

  /**
   * Get a file object relative to the current module's directory. (See @method setDirectory())
   *
   * @param string $sPath The relative or absolute path to the file
   * @return fs\file|null The file corresponding to the path given, or NULL if none found
   */
  protected function getFile($sPath, $bDebug = true) {

    $fs = $this->getControler(static::FS_CONTROLER);

    return $fs->getFile($sPath, $this->getDirectory(), $bDebug);
  }

  protected function createTempDirectory($sName = '') {

    $fs = $this->getControler('fs/editable');
    $user = $this->getControler('user');

    $tmp = $fs->getDirectory((string) $user->getDirectory('#tmp'));

    if ($sName) $result = $tmp->addDirectory($sName);
    else $result = $tmp->createDirectory();

    return $result;
  }
}


