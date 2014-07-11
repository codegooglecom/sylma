<?php

namespace sylma\storage\sql\alter;
use sylma\core, sylma\dom, sylma\storage\fs, sylma\schema, sylma\storage\xml;

class Handler extends core\module\Domed implements core\stringable {

  const ARGUMENTS = 'builder.xml';

  protected $schema;
  protected $bDepth = false;
  //protected $bLog = true;

  protected static $aFiles = array();

  public function __construct() {

    $this->setDirectory(__FILE__);
  }

  public static function reset() {

    self::$aFiles = array();
  }

  public function setSettings($args = null, $bMerge = true) {

    return parent::setSettings($args, $bMerge);
  }

  public function setFile(fs\file $file) {

    $parser = $this->getManager(self::PARSER_MANAGER);

    if (!$this->getSettings(false)) {

      $this->setSettings($this->getScript(self::ARGUMENTS));
    }

    $this->loadSchema($parser->loadBuilder($file, null, $this->getSettings()));

    return parent::setFile($file);
  }

  protected function loadSchema(schema\Builder $builder) {

    $this->setSchema($builder->getSchema());
  }

  /**
   * @param type $bDepth
   * @return boolean
   */
  /*
  public function useLog($bValue = null) {

    if (is_bool($bValue)) {

      $this->bLog = $bValue;
    }

    return $this->bLog;
  }
*/
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
/*
  public function log($val) {

    if ($this->bLog) {

      dsp($val);
    }
  }
*/
  public function asString() {

    $sFile = (string) $this->getFile('', false);

    if (!$sFile || !in_array($sFile, self::$aFiles)) {

      self::$aFiles[] = $sFile;

      $schema = $this->getSchema();
      $table = $schema->getElement();

      //$this->log($sFile);

      $table->asCreate($this->useDepth());
      $table->asUpdate();
    }

    return 'Table altered';
  }
}

