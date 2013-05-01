<?php

namespace sylma\storage\sql\template\component;
use sylma\core, sylma\template, sylma\storage\sql;

class Collection extends Rooted implements sql\template\pathable {

  protected $table;

  public function getElement($sName, $sNamespace) {

    return $this->getTable()->getElement($sName, $sNamespace);
  }

  public function setTable(Table $table) {

    $this->table = $table;
  }

  protected function getTable() {

    return $this->table;
  }

  protected function preBuild() {

    $window = $this->getWindow();

    $var = $window->createVariable('item', '\sylma\core\argument', false);
    $this->setSource($var);
  }

  protected function postBuild($result) {

    $window = $this->getWindow();

    $loop = $window->createLoop($this->getQuery()->getVar(), $this->getSource());
    $window->setScope($loop);

    $loop->addContent($this->getParser()->getView()->addToResult($result, false));
    $window->stopScope();

    $result = $loop;

    return $result;
  }

  public function reflectApplyPath(array $aPath, $sMode) {

    if (!$aPath) {

      $this->launchException('Table must not be applied (internally) without path neither template, reflectApply() should be called instead');
    }

    return $this->parsePathTokens($aPath, $sMode);
  }

  public function reflectApply($sPath = '', $sMode = '') {

    if (!$sPath) {

      if ($result = $this->lookupTemplate($sMode)) {

        $this->preBuild();
        $result->setTree($this);
      }
      else {

        $this->launchException('Cannot apply collection without template', get_defined_vars());
      }
    }
    else {

      $this->preBuild();

      $this->getQuery()->isMultiple(true);
      $this->getTable()->setSource($this->getSource());

      $content = $this->reflectApplyPath($this->parsePaths($sPath), $sMode);
      $result = $this->postBuild($content);
    }

    return $result;
  }

  public function reflectApplyAll(array $aPath, $sMode) {

    $result = $this->getTable()->reflectApply($aPath, $sMode);

    return $result;
  }
}

