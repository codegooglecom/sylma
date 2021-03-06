<?php

namespace sylma\modules\stepper\test\samples;
use sylma\core, sylma\modules\stepper;

class Stepper01 extends stepper\Browser {

  const NS = 'http://2013.sylma.org/modules/stepper';
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

  public function prepareSample() {

    $current = $this->getManager('fs')->extractDirectory(__FILE__);

    if ($sFile = $this->read('file', false)) {

      $sample = $current->getFile($sFile);
    }
    else {

      $sample = $current->getFile('sample01.tml');
    }

    $doc = $sample->asDocument();

    if ($sPath = $this->read('path', false)) {

      $page = $doc->getx('//ns:page', array('ns' => self::NS));
      $page->setAttribute('url', $sPath);
    }

    $this->clearDirectory();
    $dir = $this->getDirectory();

    $samplecopy = $dir->createFile($sample->getName());
    $doc->saveFile($samplecopy);
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

  public function getTimeshift() {

    $result = new \DateTime;
    $result->sub(new \DateInterval('P4D'));

    return $this->formatDateObject($result);
  }

  public function formatDate($sValue) {

    $date = new \DateTime($sValue);

    return $this->formatDateObject($date);
  }

  public function formatDateObject(\DateTime $date) {

    return $date->format('Y-m-d H:i');
  }

  public function testTimeshift() {


  }
}

