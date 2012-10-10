<?php

namespace sylma\parser;
use \sylma\core, sylma\parser\languages\common, sylma\storage\fs;

\Sylma::load('/core/module/Argumented.php');

abstract class Handler extends core\module\Argumented {

  protected $file;
  protected $baseDirectory = null;

  public function __construct(fs\file $file, fs\directory $dir = null) {

    $this->setFile($file);

    if ($dir) $this->setBaseDirectory($dir);
    else $this->setBaseDirectory($file->getParent());
  }

  protected function getBaseDirectory() {

    return $this->baseDirectory;
  }

  public function setBaseDirectory(fs\directory $baseDirectory) {

    $this->baseDirectory = $baseDirectory;
  }

  protected function setFile(fs\file $file) {

    $this->file = $file;
  }

  protected function getFile() {

    return $this->file;
  }

  protected function load() {

    $result = null;

    $file = $this->loadCompiler();

    if ($this->getControler()->readArgument('debug/run')) {

      if ($file) $result = $this->loadCache($file);
    }
    else {

      $this->throwException('No result, DEBUG_RUN set to TRUE');
    }

    return $result;
  }

  protected function loadCache(fs\file $file) {

    return $this->getControler()->create('cached', array($file));
  }

  protected function loadCompiler() {

    $factory = $this->getControler();
    $compiler = $factory->create('compiler', array($factory));

    if (!$file = $compiler->getCache($this->getFile())) {

      $file = $compiler->build($this->getFile(), $this->getBaseDirectory());
    }

    return $file;
  }
}
