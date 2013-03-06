<?php

namespace sylma\storage\sql\schema;
use sylma\core, sylma\dom, sylma\schema\xsd, sylma\parser\reflector;

class Handler extends xsd\Elemented {

  const NS = 'http://2013.sylma.org/storage/sql/schema';
  const PREFIX = 'sql';
  const TYPES_FILE = 'datatypes.xql';

  public function __construct(reflector\documented $root, reflector\elemented $parent = null, core\argument $arg = null) {

    parent::__construct($root, $parent, $arg);

    $this->setDirectory(__FILE__);
    $this->setNamespace(parent::NS, parent::PREFIX);
  }

  public function parseRoot(dom\element $el) {

    parent::parseRoot($el);

    $import = $this->loadSimpleComponent('component/import');
    $import->parseFile($this->getFile(self::TYPES_FILE));
  }
}

