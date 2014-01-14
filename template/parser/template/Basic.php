<?php

namespace sylma\template\parser\template;
use sylma\core, sylma\parser\languages\common, sylma\template as template_ns;

class Basic extends template_ns\parser\component\Child implements core\tokenable, common\arrayable {

  const NAME_DEFAULT = '*';

  const WEIGHT_ELEMENT = 25;
  const WEIGHT_ELEMENT_ALL = 15;
  const WEIGHT_ELEMENT_ROOT = 25;

  const MATCH_DEFAULT = '[root]';
  const MODE_DEFAULT = '';

  const CHECK_RECURSION = false; // if TRUE, disable concat optimization

  protected $aContent;
  protected $bBuilded = false;

  protected $sMatch;
  protected $aMatch = array();

  protected $tree;
  protected $bCloned = false;

  protected function setID($sID) {

    $this->sID = $sID;
  }

  public function getID() {

    return $this->sID;
  }

  public function build() {

    if (!$this->bBuilded) {

      $this->start();

      $mContent = $this->getNode()->countChildren() ? $this->parseComponentRoot($this->getNode()) : null;

      $this->stop();

      $this->aContent = is_array($mContent) ? $mContent : array($mContent);
      $this->bBuilded = true;
    }

    return $this->aContent;
  }

  protected function setMatch($sMatch) {

    $this->sMatch = $sMatch;
  }

  public function getMatch($sKey = '') {

    if ($sKey) return $this->aMatch[$sKey];
    else return $this->sMatch;
    return ;
  }

  public function setTree(template_ns\parser\tree $tree) {

    if (!$this->bCloned && $this->getMatch()) {

      $this->launchException('Template must be cloned');
    }

    if ($this->tree) {

      $this->launchException('Template is already assigned to a tree');
    }

    //$this->initComponents();

    $this->tree = $tree;
  }

  public function getTree($bDebug = true) {

    if (!$this->tree) {

      if ($bDebug) $this->launchException('No tree defined');
    }

    return $this->tree;
  }

  protected function loadMode() {

    if ($sMode = $this->readx('@mode')) {

      $this->sMode = $sMode;
    }
  }

  protected function start() {

    $this->getParser()->startTemplate($this);
  }

  protected function stop() {

    $this->getParser()->stopTemplate();
  }

  public function getMode() {

    return $this->sMode;
  }

  public function getWeight($sNamespace, $sName, $sMode = self::MODE_DEFAULT, $bRoot = false) {

    $iResult = 0;

    if ($sMode === $this->getMode()) {

      if ($bRoot && !$this->getMatch()) {

        $iResult = self::WEIGHT_ELEMENT_ROOT;
      }
      else {

        $iResult = $this->getWeightName($sNamespace, $sName);
      }
    }

    return $iResult;
  }

  public function getWeightName($sNamespace, $sName) {

    $iResult = 0;

    if ($this->getMatch() && (($sNamespace === $this->getNamespace()) || !$this->getNamespace())) {

      if ($sName === $this->getMatch('name')) {

        $iResult = self::WEIGHT_ELEMENT;
      }
      else if ($this->getMatch('name') === self::NAME_DEFAULT) {

        $iResult = self::WEIGHT_ELEMENT_ALL;
      }
    }

    return $iResult;
  }

  public function isCloned() {

    return $this->bCloned;
  }

  public function useOnce() {

    return $this->readx('@once');
  }

  protected function startLog($sMessage = '', array $aVars = array()) {

    parent::startLog($this->asToken(), array_merge(array('file' => (string) $this->getSourceFile()), $aVars));
  }

  public function asToken() {

    return 'Template ' . ($this->getMatch() ? "({$this->getMatch()})" : 'root') . ($this->getMode() ? " [mode={$this->getMode()}]" : "");
  }

  protected function initRender() {

    $this->getTree(); // exists
  }

  public function asArray() {

    if (!$this->isCloned() && $this->getMatch()) {

      $this->launchException('Template must be cloned');
    }

    $this->initRender();
    //$this->getParser()->checkTemplate($this);

    //self::$aCall[] = $this->getID();

    $this->start();
    $this->startLog();

    $aResult[] = $this->aHeaders;

    if (self::CHECK_RECURSION) $aResult[] = $this->getWindow()->toString($this->build());
    else $aResult[] = $this->getWindow()->parseArrayables($this->build());

    $this->stopLog();
    $this->stop();

    //array_pop(self::$aCall);

    return $aResult;
  }

  public function asArgument() {

    $this->launchException('Should not be called');

    return $this->getParser()->addToResult($this->asArray(), false);
  }

  public function __clone() {

    $this->bCloned = true;
  }

}

