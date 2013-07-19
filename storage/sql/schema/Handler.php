<?php

namespace sylma\storage\sql\schema;
use sylma\core, sylma\dom, sylma\schema\xsd, sylma\parser\reflector;

class Handler extends xsd\Elemented {

  protected $argPaths;
  protected $argPrevious;

  const NS = 'http://2013.sylma.org/storage/sql';
  const PREFIX = 'sql';
  const TYPES_FILE = 'datatypes.xql';

  public function __construct(reflector\documented $root, reflector\elemented $parent = null, core\argument $arg = null) {

    parent::__construct($root, $parent, $arg);

    $this->setDirectory(__FILE__);
    $this->setNamespace(parent::NS, parent::PREFIX);

    $this->argPaths = $this->getScript('/#sylma/storage/sql/view/manager.xml');

    $this->loadBaseTypes(array(
      'foreign' => self::NS,
      'reference' => self::NS,
      'collection' => self::NS,
    ));
  }

  public function changeMode($sMode) {

    $this->argPrevious = $this->getArguments();
    $class = $this->getScript($this->argPaths->read("argument/$sMode"))->get('classes/elemented');

    $this->setArguments($class, false);
  }

  public function resetMode() {

    $this->setArguments($this->argPrevious, false);
  }

  public function parseRoot(dom\element $el) {

    parent::parseRoot($el);
    $this->setNode($el, false);

    $this->setDirectory(__FILE__);

    $import = $this->loadSimpleComponent('component/import');
    $import->parseFile($this->getFile(self::TYPES_FILE));
  }

  public function getArguments() {

    return parent::getArguments();
  }

  public function setArguments($mArguments = null, $bMerge = true) {

    $this->log('Change schema arguments', array($mArguments));
    return parent::setArguments($mArguments, $bMerge);
  }

  protected function addSchemaChild(dom\element $el, $sNamespace) {

    switch ($el->getName()) {

      case 'field' :
      case 'table' :

        $sName = $this->addSchemaElement($el, $sNamespace);
        break;

      default :

        $sName = parent::addSchemaChild($el, $sNamespace);
    }

    return $sName;
  }

}

