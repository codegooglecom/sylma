<?php

namespace sylma\core\module;
use \sylma\core, \sylma\storage\fs;

abstract class Filed extends Sessioned {

  protected $directory = null;
  protected $file = null;

  const PARSER_MANAGER = 'parser';
  const FILE_MANAGER = 'fs';

  protected static $sArgumentClass = 'sylma\core\argument\Filed';
  protected static $sFactoryClass = '\sylma\core\factory\Reflector';

  protected static $sArgumentXMLClass = '\sylma\core\argument\parser\Handler';

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

  private function createArgumentFromString($sPath, $sNamespace = '') {

    $file = $this->getManager(self::FILE_MANAGER)->getFile($sPath, $this->getDirectory('', false));

    if ($file->getExtension() === 'xml') {

      //static::$sFactoryClass = '\sylma\core\factory\Cached';
      $result = $this->getManager(self::PARSER_MANAGER)->load($file);
    }
    else {

      $result = parent::createArgument((string) $file, $sNamespace);
    }

    return $result;
  }

  protected function setArguments($mArguments = null, $bMerge = true) {

    if (is_string($mArguments)) {

      $mArguments = $this->createArgumentFromString($mArguments, $this->getNamespace());
    }

    parent::setArguments($mArguments, $bMerge);

    return $this->getArguments();
  }

  protected function setSettings($args = null, $bMerge = true) {

    if (is_string($args)) {

      $args = $this->createArgumentFromString($args, $this->getNamespace());
    }

    parent::setSettings($args, $bMerge);

    return $this->getSettings(false);
  }

  /**
   * Factory connection
   *
   * @param string $sName
   * @param array $aArguments
   * @param string $sDirectory
   * @return mixed
   */
  public function create($sName, array $aArguments = array(), $sDirectory = '') {

    if (!$sDirectory) $sDirectory = $this->getDirectory('', false);

    return parent::create($sName, $aArguments, $sDirectory);
  }

  /**
   * Set the current directory
   *
   * @param fs\directory|string $mDirectory An object or string to set as default directory can be used with (__FILE__ or get_class($this))
   * @return fs\directory
   */
  protected function setDirectory($mDirectory) {

    if (is_string($mDirectory)) {

      $fs = $this->getManager('fs');
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

      $result = \Sylma::getManager(static::FILE_MANAGER);
    }
    else {

      $result = parent::loadControler($sName);
    }

    return $result;
  }

  /**
   * @return \sylma\storage\fs\directory The current directory
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
   * If no path sent, try to get local file set with @method setFile()
   *
   * @param string $sPath The relative or absolute path to the file
   * @return \sylma\storage\fs\file|null The file corresponding to the path given, or NULL if none found
   */
  protected function getFile($sPath = '', $bDebug = true) {

    if ($sPath) {

      $fs = $this->getManager(static::FILE_MANAGER);
      $result = $fs->getFile($sPath, $this->getDirectory(), $bDebug);
    }
    else {

      if (!$this->file && $bDebug) {

        $this->throwException('No file associated to this object [' . get_class($this) . ']');
      }

      $result = $this->file;
    }

    return $result;
  }

  /**
   * Set a local file (exists mainly cause of @method getFile())
   * @param \sylma\storage\fs\file $file
   * @return string
   */
  protected function setFile(fs\file $file) {

    $this->file = $file;
  }

  protected function createTempDirectory($sName = '') {

    $fs = $this->getManager('fs/editable');
    $user = $this->getManager('user');

    $tmp = $fs->getDirectory((string) $user->getDirectory('#tmp'));

    if ($sName) $result = $tmp->addDirectory($sName);
    else $result = $tmp->createDirectory();

    return $result;
  }
}


