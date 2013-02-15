<?php

namespace sylma\core\factory\parser\test;
use \sylma\modules\tester, \sylma\core, \sylma\dom, \sylma\storage\fs, \sylma\core\argument\parser;

class Basic extends parser\test\Basic {

  const PARSER_NS = 'http://www.sylma.org/core/argument';
  const PARSER_PREFIX = 'arg';

  protected static $sFactoryFile = '/core/factory/Cached.php';
  protected static $sFactoryClass = '\sylma\core\factory\Cached';

  public function __construct() {

    $this->setNamespace(self::PARSER_PREFIX, self::PARSER_NS);

    parent::__construct();

    $this->setDirectory(__FILE__);

    $cache = \Sylma::getManager('fs/cache');
    $this->exportDirectory = $cache->getDirectory()->addDirectory((string) $this->getDirectory());

    $this->setFiles(array($this->getFile('basic.xml')));
  }

  public function create($sName, array $aArguments = array(), $sDirectory = '') {

    $factory = $this->createFactory($this->getArgument('arg'));

    return $factory->create($sName, $aArguments, $sDirectory);
  }
}

