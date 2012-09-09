<?php

namespace sylma\parser\compiler;
use sylma\core, sylma\parser, sylma\storage\fs, sylma\dom;

require_once('core/module/Domed.php');

abstract class Basic extends core\module\Domed {

  const PHP_TEMPLATE = '/#sylma/parser/languages/php/source.xsl';
  const EXTENSION_DEFAULT = '.php';

  public function getCache(fs\file $file) {

    $result = null;

    $tmpDir = $this->getCachedDirectory($file);

    if ($tmpDir) {

      $tmpFile = $this->getCachedFile($file, self::EXTENSION_DEFAULT, fs\resource::DEBUG_NOT);
    }

    if ($tmpDir && $tmpFile && $tmpFile->getLastChange() > $file->getLastChange() && !$this->getControler()->readArgument('debug/update')) {

      $result = $tmpFile;
    }

    return $result;
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
    $factory = $this->getControler();

    $result = $factory->create('reflector', array($factory, $doc, $base));

    return $result;
  }
}