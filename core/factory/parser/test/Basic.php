<?php

namespace sylma\core\factory\parser\test;
use \sylma\modules\tester, \sylma\core, \sylma\dom, \sylma\storage\fs, \sylma\core\argument\parser;

class Basic extends parser\test\Basic {

  const PARSER_NS = 'http://www.sylma.org/core/argument';
  const PARSER_PREFIX = 'arg';

  protected static $sFactoryFile = '/core/factory/Cached.php';
  protected static $sFactoryClass = '\sylma\core\factory\Cached';

  protected $factoryTest;

  public function __construct() {

    $this->setNamespace(self::PARSER_PREFIX, self::PARSER_NS);

    parent::__construct();

    $this->setDirectory(__FILE__);

    $cache = \Sylma::getManager('fs/cache');
    $this->exportDirectory = $cache->getDirectory()->addDirectory((string) $this->getDirectory());

    $this->setFiles(array($this->getFile('basic.xml')));
  }

  protected function testArgument(dom\element $test, dom\document $doc, fs\file $file) {

    $this->factoryTest = $this->createFactory($this->getArgument('arg'));

    return parent::testArgument($test, $doc, $file);
  }

  public function create($sName, array $aArguments = array(), $sDirectory = '') {

    return $this->factoryTest->create($sName, $aArguments, $sDirectory);
  }

  public function findClass($sName) {

    return $this->factoryTest->findClass($sName);
  }

  public function getArgument($sPath, $bDebug = true, $mDefault = null) {

    return parent::getArgument($sPath, $bDebug, $mDefault);
  }
}

