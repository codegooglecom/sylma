<?php

namespace sylma\storage\sql\alter;
use sylma\core, sylma\dom, sylma\storage\fs, sylma\schema;

class Handler extends core\module\Domed implements core\stringable {

  const ARGUMENTS = 'builder.xml';

  protected $schema;

  public function __construct() {

    $this->setDirectory(__FILE__);
  }

  public function setFile(fs\file $file) {

    $parser = $this->getManager(self::PARSER_MANAGER);
    $builder = $parser->loadBuilder($file, null, $this->getScript(self::ARGUMENTS));
    $result = $builder->getSchema();

    $this->setSchema($result);
  }

  protected function setSchema(schema\parser\schema $schema) {

    $this->schema = $schema;
  }

  protected function getSchema() {

    return $this->schema;
  }

  public function setDocument(dom\handler $doc) {

    $sNamespace = $doc->getRoot()->getNamespace();
    $parser = $this->getManager(self::PARSER_MANAGER);
    $builder = $parser->loadBuilderFromNS($sNamespace, null, null, $this->getScript(self::ARGUMENTS));
    $builder->setDocument($doc);

    $result = $builder->getSchema();
    $this->setSchema($result);
  }

  public function asString() {

    $sql = $this->getManager('mysql');

    $schema = $this->getSchema();
    $table = $schema->getElement();

    dsp($table->asCreate());

    $sql->read($table->asCreate());

    dsp($table->asUpdate());

    $sql->read($table->asUpdate());
  }
}

