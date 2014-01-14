<?php

namespace sylma\core\argument\parser\test;
use \sylma\modules\tester, \sylma\core, \sylma\dom, \sylma\storage\fs;

class Basic extends tester\Basic implements core\argumentable {

  const NS = 'http://www.sylma.org/modules/tester';

  const PARSER_NS = 'http://2013.sylma.org/core/argument';
  const PARSER_PREFIX = 'arg';

  protected static $sArgumentClass = 'sylma\core\argument\Readable';

  protected $sTitle = 'Grouped';
  protected $exportDirectory;

  public function __construct() {

    $this->setDirectory(__file__);
    $this->setNamespaces(array(
      'self' => self::NS,
      self::PARSER_PREFIX => self::PARSER_NS,
    ));

    $this->setManager($this->getManager('parser'));

    $cache = \Sylma::getManager('fs/cache');
    $this->exportDirectory = $cache->getDirectory()->addDirectory((string) $this->getDirectory());

    $this->setArguments(array());
    $this->setFiles(array($this->getFile('basic.xml')));
  }

  public function buildArgument(dom\element $arg, fs\file $file, dom\element $test) {

    require_once('core/functions/Path.php');
    $sName = core\functions\path\urlize($file->getName() . '-' . $test->readAttribute('name'));

    $tmp = $this->exportDirectory->createFile($sName . '.xml');
    $doc = $this->getManager('dom')->createDocument($arg);

    $doc->saveFile($tmp, true);

    $result = $this->getManager()->load($tmp, array(), true);
    //$result->setBaseDirectory($file->getParent());

    return $result;
  }

  protected function test(dom\element $test, $sContent, $controler, dom\document $doc, fs\file $file) {

    $bResult = null;
    $node = $test->getx('arg:argument');

    try {

      $this->setArgument('arg', $this->buildArgument($node, $file, $test));

      $bResult = $this->testArgument($test, $doc, $file);
    }
    catch (core\exception $e) {

      $bResult = $this->catchException($test, $e, $file);
    }

    return $bResult;
  }

  protected function testArgument(dom\element $test, dom\document $doc, fs\file $file) {

    $expected = $test->getx('self:expected');

    return parent::test($test, $expected->read(), $this, $doc, $file);
  }

  public function getArgument($sPath, $bDebug = true, $mDefault = null) {

    return parent::getArgument($sPath, $bDebug, $mDefault);
  }
}

