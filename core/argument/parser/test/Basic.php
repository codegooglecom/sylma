<?php

namespace sylma\core\argument\parser\test;
use \sylma\modules\tester, \sylma\core, \sylma\dom, \sylma\storage\fs, \sylma\parser;

class Basic extends tester\Basic {

  const NS = 'http://www.sylma.org/core/argument/parser/test';
  const ARGUMENT_NS = 'http://www.sylma.org/core/argument';

  protected $sTitle = 'Grouped';
  protected $exportDirectory;

  public function __construct() {

    $this->setDirectory(__file__);
    $this->setNamespaces(array(
      'self' => self::NS,
      'arg' => self::ARGUMENT_NS,
    ));

    $this->setControler($this->getControler('argument/parser'));

    $cache = \Sylma::getControler('fs/cache');
    $this->exportDirectory = $cache->getDirectory()->addDirectory((string) $this->getDirectory());

    $this->setArguments(array());
    $this->setFiles(array($this->getFile('basic.xml')));
  }

  public function buildArgument(dom\element $arg, fs\file $file, dom\element $test) {

    require_once('core/functions/Path.php');
    $sName = core\functions\path\urlize($file->getName() . '-' . $test->readAttribute('name'));

    $tmp = $this->exportDirectory->createFile($sName . '.xml');
    $doc = $this->getControler('dom')->createDocument($arg);

    $doc->saveFile($tmp, true);

    $result = $this->getControler()->createArguments($tmp);
    //$result->setBaseDirectory($file->getParent());

    return $result;
  }

  protected function test(dom\element $test, $sContent, $controler, dom\document $doc, fs\file $file) {

    $bResult = null;
    $node = $test->getx('arg:argument');

    try {

      //$controler->buildAction($this->createDocument($node), $aArguments, $this->exportDirectory, $file->getParent(), $sName);
      $this->setArgument('arg', $this->buildArgument($node, $file, $test));

      $expected = $test->getx('self:expected');
      $bResult = parent::test($test, $expected->read(), $this, $doc, $file);
    }
    catch (core\exception $e) {

      $e->save();
    }

    return $bResult;
  }

  public function getArgument($sPath, $mDefault = null, $bDebug = false) {

    return parent::getArgument($sPath, $mDefault, $bDebug);
  }
}

