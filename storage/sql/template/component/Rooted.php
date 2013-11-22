<?php

namespace sylma\storage\sql\template\component;
use sylma\core, sylma\dom, sylma\storage\sql, sylma\schema\parser, sylma\parser\languages\common;

class Rooted extends sql\schema\component\Table {

  protected $source;
  protected $key;

  protected $query;
  protected $bInsertQuery = true;
  protected $bOptional = false;

  protected $aTriggers = array();

  public function setSource($source) {

    $this->source = $source;
  }

  protected function getSource() {

    return $this->source;
  }

  protected function setKey(common\_var $key) {

    $this->key = $key;
  }

  protected function getKey() {

    return $this->key;
  }

  public function setQuery(sql\query\parser\Basic $query) {

    $this->query = $query;
  }

  public function getQuery() {

    if (!$this->query) {

      $this->launchException('No query defined');
    }

    return $this->query;
  }

  /**
   * @todo rename to isQueried()
   */
  public function insertQuery($bVal = null) {

    if (is_bool($bVal)) $this->bInsertQuery = $bVal;

    return $this->bInsertQuery;
  }

  protected function parsePaths($sPath, $sMode) {

    $aResult = $this->getParser()->parsePath($sPath, $sMode);

    return $aResult;
  }

  protected function lookupTemplate($sMode) {

    return $this->getParser()->lookupTemplate($this, 'element', $sMode, $this->isRoot());
  }

  protected function parsePathTokens($aPath, $sMode) {

    return $this->getParser()->parsePathTokens($this, $aPath, $sMode);
  }

  public function addTrigger(array $aContent) {

    //return $this->getWindow()->createGroup($aContent);
    return $aContent;
  }

  protected function getTriggers() {

    return $this->aTriggers;
  }

  public function setOptional($bValue) {

    $this->bOptional = $bValue;
  }

  public function getResult() {

    return $this->getParser()->getView()->getResult();
  }

  public function getToken() {

    return $this->createObject('token');
  }
}

