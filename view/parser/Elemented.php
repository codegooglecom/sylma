<?php

namespace sylma\view\parser;
use sylma\core, sylma\dom, sylma\template, sylma\parser\reflector;

class Elemented extends template\parser\Elemented {

  const NS = 'http://2013.sylma.org/view';
  const NS_SCHEMA = 'http://2013.sylma.org/schema/template';

  protected $schema;
  protected $resource;

  protected $allowForeign = true;

  public function __construct(reflector\documented $root, reflector\elemented $parent = null, core\argument $arg = null) {

    //$this->setNamespace(self::NS, self::PREFIX);
    $this->setNamespace(self::NS_SCHEMA);
    $this->setNamespace(parent::NS, parent::PREFIX);

    parent::__construct($root, $parent, $arg);
  }

  public function parseRoot(dom\element $el) {

    $this->setNode($el);

    if (!$el->getName() == 'view') {

      $this->throwException('Bad root');
    }

    $this->loadResult();

    $resource = $this->parseElement($el->getx('*[local-name() = "resource"]'));
    $schema = $resource->setSchema($this->loadSchema());

    $this->loadTemplates();
    $schema->loadTemplates($this->getTemplates());

    $tpl = $this->getTemplate();
    $tpl->setTree($resource->getTree());

    //return $tpl->asArgumentable();
    $this->addToResult(array($tpl));

    return $this->getResult();
  }

  protected function loadSchema() {

    $component = $this->loadSimpleComponent('component/schema', $this);
    $result = $component->parseRoot($this->getNode()->getx('self:schema'));

    return $result;
  }

  public function setSchema(fs\file $schema) {

    $this->schema = $schema;
  }

  protected function getSchema() {

    return $this->schema;
  }
}

