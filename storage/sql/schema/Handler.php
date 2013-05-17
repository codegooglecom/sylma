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

    $this->loadBaseTypes(array('foreign' => self::NS));
  }

  public function parseRoot(dom\element $el) {

    parent::parseRoot($el);
    $this->setNode($el, false);

    $this->setDirectory(__FILE__);

    $import = $this->loadSimpleComponent('component/import');
    $import->parseFile($this->getFile(self::TYPES_FILE));
  }

  protected function addSchemaChild(dom\element $el, $sNamespace) {

    switch ($el->getName()) {

      case 'field' :
      case 'table' :

        $this->addSchemaElement($el, $sNamespace);
        break;

      default :

        parent::addSchemaChild($el, $sNamespace);
    }
  }

}

