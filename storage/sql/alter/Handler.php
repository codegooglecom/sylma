<?php

namespace sylma\storage\sql\alter;
use sylma\core, sylma\dom, sylma\storage\fs, sylma\schema;

class Handler extends core\module\Domed implements core\stringable {

  const ARGUMENTS = 'builder.xml';

  protected $schema;
  protected $bDepth = false;

  protected static $aFiles = array();

  public function __construct() {

    $this->setDirectory(__FILE__);
  }

  public function setFile(fs\file $file) {

    $parser = $this->getManager(self::PARSER_MANAGER);
    $builder = $parser->loadBuilder($file, null, $this->getScript(self::ARGUMENTS));
    $result = $builder->getSchema();

    $this->setSchema($result);

    return parent::setFile($file);
  }

  /**
   * @param type $bDepth
   * @return boolean
   */
  public function useDepth($bDepth = null) {

    if (!is_null($bDepth)) $this->bDepth = (bool) $bDepth;

    return $this->bDepth;
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

    $sFile = (string) $this->getFile('', false);

    if (!$sFile || !in_array($sFile, self::$aFiles)) {

      self::$aFiles[] = $sFile;

      $schema = $this->getSchema();
      $table = $schema->getElement();

      dsp($sFile);

      $table->asCreate($this->useDepth());
      $table->asUpdate();
    }
  }
}

