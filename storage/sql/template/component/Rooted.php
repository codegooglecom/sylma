<?php

namespace sylma\storage\sql\template\component;
use sylma\core, sylma\dom, sylma\storage\sql, sylma\schema\parser, sylma\parser\languages\common;

class Rooted extends sql\schema\component\Table {

  protected $source;

  protected $query;
  protected $bInsertQuery = true;

  protected $bRoot = false;
  protected $aTriggers = array();

  public function isRoot($bValue = null) {

    if (is_bool($bValue)) $this->bRoot = $bValue;

    return $this->bRoot;
  }

  public function setSource($source) {

    $this->source = $source;
  }

  protected function getSource() {

    return $this->source;
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

  public function getResult() {

    return $this->getParser()->getView()->getResult();
  }

  public function getToken() {

    return $this->createObject('token');
  }
}

