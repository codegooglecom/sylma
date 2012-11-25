<?php

namespace sylma\parser\compiler;
use sylma\core, sylma\parser, sylma\storage\fs, sylma\dom;

abstract class Manager extends core\module\Domed {

  const PHP_TEMPLATE = '/#sylma/parser/languages/php/source.xsl';
  const EXTENSION_DEFAULT = '.php';

  const WINDOW_ARGS = 'classes/php';

  protected $baseDirectory = null;

  public function __construct(core\argument $arguments = null) {

    //$this->setDirectory(__FILE__);

    if (!$arguments && $this->getDirectory()) {

      $arguments = 'manager.xml';
    }

    $this->setArguments($arguments);

    $this->loadDefaultArguments();
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

  protected function createReflector(fs\file $file, fs\directory $base) {

    $doc = $file->getDocument(array(), \Sylma::MODE_EXECUTE);

    $result = $this->create('reflector', array($this, $doc, $base));

    return $result;
  }

  protected function load(fs\file $file, array $aArguments = array()) {

    $result = null;
    $cache = $this->loadCache($file);

    if ($this->readArgument('debug/run')) {

      if ($cache) {

        array_unshift($aArguments, $cache);
        $result = $this->createCache($aArguments);
      }
    }
    else {

      $this->throwException('No result, DEBUG_RUN set to TRUE');
    }

    return $result;
  }

  /**
   * Read cache or build file
   *
   * @param \sylma\storage\fs\file $file
   * @param \sylma\storage\fs\directory $dir
   *
   * @return fs\file
   */
  protected function loadCache(fs\file $file) {

    if ((!$result = $this->getCache($file)) && $this->getControler('user')->isPrivate()) {

      $result = $this->build($file, $file->getParent());
    }

    return $result;
  }

  /**
   * Search then read cache file if exists
   *
   * @param fs\file $file
   *
   * @return fs\file
   */
  protected function getCache(fs\file $file) {

    $result = null;

    $tmpDir = $this->getCachedDirectory($file);
    $tmpFile = null;

    if ($tmpDir) {

      $tmpFile = $this->getCachedFile($file, static::EXTENSION_DEFAULT, fs\resource::DEBUG_NOT);
    }

    if (!$this->getControler('user')->isPrivate()) {

      $result = $tmpFile;
    }
    else {

      $bUpdate = $this->readArgument('debug/update');

      if ($tmpFile && !$bUpdate && $tmpFile->getLastChange() > $file->getLastChange()) {

        $result = $tmpFile;
      }
    }

    return $result;
  }

  protected function createCache(array $aArguments = array()) {

    return $this->create('cached', $aArguments);
  }

  protected function build(fs\file $file, fs\directory $dir) {

    $reflector = $this->createReflector($file, $dir);

    $window = $this->runReflector($reflector, $this->readArgument('classes\cached'), $file);

    if ($this->readArgument('debug/show')) {

      $tmp = $this->create('document', array($window));
      echo '<pre>' . $file->asToken() . '</pre>';
      echo '<pre>' . str_replace(array('<', '>'), array('&lt;', '&gt'), $tmp->asString(true)) . '</pre>';
    }

    $result = $this->getCachedFile($file);
    $template = $this->getTemplate(static::PHP_TEMPLATE);

    $sContent = $template->parseDocument($window, false);
    $result->saveText($sContent);

    return $result;
  }

  protected function runReflector(parser\reflector\documented $reflector, $sInstance, fs\file $file) {

    try {

      $window = $this->create('window', array($reflector, $this->getArgument(static::WINDOW_ARGS), $sInstance));
      $reflector->setWindow($window);

      $result = $reflector->asDOM();
    }
    catch (core\exception $e) {

      $e->addPath($file->asToken());
      throw $e;
    }

    return $result;
  }

}