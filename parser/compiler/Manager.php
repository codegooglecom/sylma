<?php

namespace sylma\parser\compiler;
use sylma\core, sylma\storage\fs;

class Manager extends core\module\Domed {

  const EXTENSION_DEFAULT = '.php';
  const ARGUMENTS = 'manager.xml';

  protected $baseDirectory = null;

  /**
   * order of arguments merge : domed, argument, directory
   * @param $arg
   */
  public function __construct(core\argument $arg = null) {

    $this->loadDefaultArguments();
    if ($arg) $this->setArguments($arg);

    if ($arg && $sDirectory = $arg->read('directory', null, false)) {

      $dir = $this->getManager(self::FILE_MANAGER)->getDirectory($sDirectory);
      $this->setDirectory($dir);
    }

    if ($this->getDirectory('', false)) {

      if ($file = $this->getFile(static::ARGUMENTS, false)) {

        $manager = $this->getManager(self::ARGUMENT_MANAGER);
        $this->setArguments($manager->createArguments($file));
      }
    }
  }

  public function getClassName($sClass) {

    $sClass = 'classes/' . $sClass . '/name';

    return $this->readArgument($sClass);
  }

  protected function getCachedDirectory(fs\file $file) {

    $fs = $this->getControler('fs/cache');

    return $fs->getDirectory()->addDirectory((string) $file->getParent());
  }

  protected function getCachedFile(fs\file $file, $sExtension = self::EXTENSION_DEFAULT, $iDebug = fs\resource::DEBUG_EXIST) {

    $sName = $file->getName() . $sExtension;

    return $this->getCachedDirectory($file)->getFile($sName, $iDebug);
  }

  public function load(fs\file $file, array $aArguments = array()) {

    $result = null;
    $cache = $this->loadCache($file);

    if ($this->readArgument('debug/run')) {

      if ($cache) {

        $result = $this->createCache($cache, $aArguments);
      }
    }
    else {

      $this->throwException('No result, DEBUG_RUN set to TRUE');
    }

    return $result;
  }

  /**
   * Load cache file. Built it if it doesn't exists
   *
   * @param $file
   * @return fs\file
   */
  protected function loadCache(fs\file $file) {

    if ((!$result = $this->getCache($file)) && $this->getControler('user')->isPrivate()) {

      $result = $this->build($file, $file->getParent());
    }

    return $result;
  }

  public function build(fs\file $file, fs\directory $dir) {

    $this->throwException('This manager cannot build');
  }

  /**
   * Search then read cache file if exists
   *
   * @param $file
   * @return fs\file
   */
  protected function getCache(fs\file $file) {

    $result = null;

    $tmpDir = $this->getCachedDirectory($file);
    $tmpFile = null;

    if ($tmpDir) {

      $tmpFile = $this->getCachedFile($file, static::EXTENSION_DEFAULT, fs\resource::DEBUG_NOT);
    }

    if ($this->getControler('user')->isPrivate()) {

      $bUpdate = $this->readArgument('debug/update');

      if ($tmpFile && !$bUpdate && $tmpFile->getLastChange() > $file->getLastChange()) {

        $result = $tmpFile;
      }
    }
    else {

      $result = $tmpFile;
    }

    return $result;
  }

  /**
   * Create cache object
   *
   * @param $file Script
   * @param array $aArguments
   * @return \sylma\parser\cached\documented
   */
  protected function createCache(fs\file $file, array $aArguments = array()) {

    return include($file->getRealPath());
  }
}