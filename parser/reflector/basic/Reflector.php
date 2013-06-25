<?php

namespace sylma\parser\reflector\basic;
use \sylma\core, sylma\dom, sylma\storage\fs;

abstract class Reflector extends Foreigner {

  protected $sourceFile;

  public function getSourceDirectory($sPath = '') {

    if ($source = $this->loadSourceFile()) {

      $manager = $this->getManager(self::FILE_MANAGER);
      $result = $sPath ? $manager->getDirectory($sPath, $source->getParent()) : $source->getParent();
    }
    else {

      $result = $this->lookupSourceDirectory($sPath);
    }

    return $result;
  }

  abstract protected function lookupSourceDirectory($sPath);

  public function getSourceFile($sPath = '', $bElement = true) {

    if ($bElement and $source = $this->loadSourceFile()) {

      $manager = $this->getManager(self::FILE_MANAGER);
      $result = $sPath ? $manager->getFile($sPath, $source->getParent()) : $source;
    }
    else {

      $result = $this->lookupSourceFile($sPath);
    }

    return $result;
  }

  abstract protected function lookupSourceFile($sPath);

  protected function loadSourceFile() {

    if (is_null($this->sourceFile)) {

      if ($this->getNode(false) and $sSource = $this->readx('@build:source', false, array('build' => self::BUILDER_NS))) {

        $result = $this->lookupSourceFile($sSource);
      }
      else {

        $result = false;
      }

      $this->sourceFile = is_null($result) ? false : $result;
    }

    return $this->sourceFile;
  }
}
