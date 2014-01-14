<?php

namespace sylma\core\window\classes;
use sylma\core, sylma\storage\fs;

class Image extends File {

  public function asString() {

    if ($sSize = $this->getManager('path')->readArgument('size', false)) {

      $size = $this->get('size/' . $sSize);

      $file = $this->getFile();

      $fs = $this->getControler('fs/cache');
      $dir = $fs->getDirectory()->addDirectory((string) $file->getParent());

      $sName = "{$file->getSimpleName()}_$sSize.{$file->getExtension()}";
      $cache = $dir->getFile($sName, fs\resource::DEBUG_EXIST);

      if (!$cache->doExist() || $cache->getLastChange() < $file->getLastChange() || $this->read('rebuild')) {

        $builder = $this->create('builder', array($file));
        $builder->build($cache, $size->read('width'), $size->read('height'));
      }

      $this->setFile($cache);
    }

    return parent::asString();
  }
}
