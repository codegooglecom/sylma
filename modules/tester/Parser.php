<?php

namespace sylma\modules\tester;
use sylma\core, sylma\dom, sylma\modules\tester, sylma\storage\fs;

class Parser extends tester\Prepare {

  const NS = 'http://www.sylma.org/modules/tester/parser';
  const TRASH_MANAGER = 'fs/trash';

  protected $exportDirectory;

  public function __construct() {

    $this->setNamespace(self::NS, self::PREFIX);

    $this->setControler($this);

    $this->exportDirectory = $this->loadCacheDirectory($this->getDirectory());

    $this->setArguments(array());
    //$this->setFiles(array($this->getFile('basic.xml')));
  }

  protected function loadCacheDirectory(fs\directory $dir) {

    $cache = $this->getManager('fs/cache');
    $result = $cache->getDirectory()->addDirectory((string) $dir);

    return $result;
  }

  public function getExportDirectory() {

    if (!$this->exportDirectory) {

      $this->throwException('No export directory');
    }

    return $this->exportDirectory;
  }

  public function setExportDirectory(fs\directory $exportDirectory) {

    $this->exportDirectory = $exportDirectory;
  }

  protected function parseResult(dom\element $test, fs\file $file, array $aArguments = array()) {

    $document = $test->getx('self:document');

    require_once('core/functions/Path.php');
    $sName = core\functions\path\urlize($file->getName() . '-' . $test->readx('@name'));

    $cache = $this->getExportDirectory()->createFile($sName);
    $cache->saveText((string) $this->createDocument($document->getFirst()));

    $manager = $this->getManager(self::PARSER_MANAGER);
    $result = $this->buildResult($manager, $cache, $aArguments);

    $this->setArgument('result', $result);

    return $result;
  }

  protected function buildResult($manager, fs\file $file, array $aArguments) {

    $manager->build($file, $this->getDirectory());

    return $this->loadResult($manager, $file, $aArguments);
  }

  protected function loadResult($manager, fs\file $file, array $aArguments) {

    $result = $manager->load($file, $aArguments, false);
    $file->delete();

    return $result;
  }

  protected function loadResultNode(dom\element $test) {

    $el = $test->getx('self:node', array(), false);
    $this->setArgument('node', $el->getFirst());
  }

  protected function test(dom\element $test, $sContent, $controler, dom\document $doc, fs\file $file) {

    try {

      $this->parseResult($test, $file);
    }
    catch (core\exception $e) {

      $e->addPath('Test ID : ' . $test->readx('@name'));
      $e->addPath($file->asToken());

      $e->save(false);

      return false;
    }

    $this->loadResultNode($test);
    return parent::test($test, $sContent, $controler, $doc, $file);
  }

  public function getArgument($sPath, $bDebug = true, $mDefault = null) {

    return parent::getArgument($sPath, $bDebug, $mDefault);
  }

  public function setArgument($sPath, $mValue) {

    return parent::setArgument($sPath, $mValue);
  }
}

