<?php

namespace sylma\storage\sql\view;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\template, sylma\storage\fs, sylma\storage\sql;

/**
 * Load query
 * Load schema by sending arguments
 */
class Resource extends reflector\handler\Elemented implements reflector\elemented {

  protected $ID;
  protected $tree;
  protected $sSource;
  protected $query;
  protected $schema;

  const NS = 'http://2013.sylma.org/storage/sql';
  const PREFIX = 'sql';

  public function parseRoot(dom\element $el) {

    $this->setDirectory(__FILE__);

    $this->setNamespace($el->getNamespace(), self::PREFIX);

    $el = $this->setNode($el);
    $this->allowForeign(true);

    //$this->parseSource();

    return $this;
  }

  protected function addID() {

    $row = $this->getTree();

    if ($id = $this->getx('sql:id')) {

      $query = $row->getQuery();

      $this->parseID($id);
      $query->setWhere($row->getElement('id', $row->getNamespace()), '=', $this->getID());
      //$query->isMultiple(false);
    }

    //$this->setQuery($query);
  }

  public function setMode($sMode) {

    $this->setArguments($this->readArgument("argument/$sMode"));
  }

  protected function build(fs\file $schema) {

    $builder = $this->getManager(self::PARSER_MANAGER)->loadBuilder($schema, null, $this->getArguments());
    $builder->setWindow($this->getWindow());

    if ($log = $this->getRoot()->getLogger(false)) {

      $builder->setLogger($log);
    }

    $schema = $builder->getSchema();
    $schema->setView($this->getParent());

    $root = $schema->getElement();
    $root->init();

    $this->setTree($root);

    if ($sTable = $this->readx('sql:table')) {

      $root->setName($sTable);
    }

    if ($connection = $this->getx('sql:connection')) {

      $this->parseComponent($connection);
    }

    if ($this->readx('@multiple')) {

      $collection = $schema->createCollection();

      //$collection->setQuery($root->getQuery());
      $collection->setTable($root);

      $this->setTree($collection);
    }

    if ($this->readx('@optional')) {

      $root->setOptional(true);
    }

    $this->addID();
    $this->getTree()->isRoot(true);

    return $schema;
  }

  /**
   *
   * @param \sylma\storage\fs\file $file
   * @return \sylma\schema\parser\schema
   */
  public function setSchema(fs\file $file) {

    $this->schema = $this->build($file);

    return $this->getSchema();
  }

  protected function setTree(sql\template\component\Rooted $tree) {

    $this->tree = $tree;
  }

  public function getTree() {

    return $this->tree;
  }

  protected function getSchema() {

    return $this->schema;
  }

  public function getCurrentTree() {

    $result = null;

    if ($tpl = $this->getSchema()->getView()->getCurrentTemplate(false)) {

      $result = $tpl->getTree(false);
    }

    if (!$result) {

      $result = $this->getTree();
    }

    return $result;
  }

  protected function parseSource() {

    //$this->dsp($this->getNS());
    //$this->dsp($this->elementDocument);
    //$this->dsp($this->elementDocument->getNS());
    //$this->dsp($this->getElement());
    //$this->dsp($this->elementDocument->getRoot());

    $this->setSource($this->getNode()->readx('self:source'));
  }

  protected function setSource($sSource) {

    $this->sSource = $sSource;
  }

  protected function getSource() {

    return $this->sSource;
  }

  protected function parseID(dom\element $el) {

    if ($el->isComplex()) {

      if ($el->countChildren() > 1) {

        $this->throwException(sprintf('Only one child expected in %s', $el->asToken()));
      }

      $mResult = $this->parseElement($el->getFirst());
    }
    else {

      $mResult = $el->read();
    }

    $this->setID($mResult);
  }

  protected function setID($id) {

    $this->ID = $id;
  }

  protected function getID() {

    return $this->ID;
  }
}

