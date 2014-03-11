<?php

namespace sylma\modules\stepper\test\samples;
use sylma\core, sylma\modules\stepper;

class Stepper01 extends stepper\Browser {

  const DIRECTORY = '/test/tmp';

  public function __construct(core\argument $args, core\argument $post) {

    parent::__construct($args, $post);

    if (!$this->getDirectory('', false)) {

      $this->setDirectory($this->getManager(self::FILE_MANAGER)->getDirectory(self::DIRECTORY));
    }
  }

  public function clearDirectory() {

    $dir = $this->getDirectory();
    $aItems = array_merge($dir->getFiles(), $dir->getDirectories());

    foreach ($aItems as $item) {

      //dsp($item);
      $item->delete();
    }
  }

  public function prepareCollection() {

    $current = $this->getManager('fs')->extractDirectory(__FILE__);
    $sample = $current->getFile('collection_01.tml');

    $this->clearDirectory();
    $dir = $this->getDirectory();

    $dir->addDirectory('sub1');
    $dir->addDirectory('sub2');

    $samplecopy = $dir->createFile($sample->getName());
    $samplecopy->saveText($sample->execute());
  }
}

