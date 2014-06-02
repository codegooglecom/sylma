<?php

namespace sylma\template\parser\template;
use sylma\core, sylma\parser\languages\common, sylma\template as template_ns;

class Basic extends template_ns\parser\component\Child implements core\tokenable, common\arrayable {

  const NAME_DEFAULT = '*';

  const WEIGHT_ELEMENT = 25;
  const WEIGHT_ELEMENT_ALL = 15;
  const WEIGHT_ELEMENT_NS = 20;
  const WEIGHT_ELEMENT_ROOT = 25;

  const MATCH_DEFAULT = '[root]';
  const MODE_DEFAULT = '';

  const CHECK_RECURSION = false; // if TRUE, disable concat optimization

  private $aContent;
  protected $aHeaders = array();

  protected $bBuilded = false;

  protected $sMatch;
  protected $aMatch = array();
  protected $sMode = '';
  protected $sXMode = '';

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

      $this->bBuilded = true;

      $this->start();

      $mContent = $this->getNode()->countChildren() ? $this->parseComponentRoot($this->getNode()) : null;

      $this->stop();

      $this->aContent = is_array($mContent) ? $mContent : array($mContent);
    }

    return $this->aContent;
  }

  protected function setMatch($sMatch) {

    $this->sMatch = $sMatch;
  }

  public function getMatch() {

    return $this->aMatch;
  }

  protected function getMatchString() {

    return $this->sMatch;
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

  protected function loadModes() {

    if ($sMode = $this->readx('@mode')) {

      $this->sMode = $sMode;
    }

    if ($sXMode = $this->readx('@xmode')) {

      $this->sXMode = $sXMode;
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

  protected function getXMode() {

    return $this->sXMode;
  }

  public function getWeight($sNamespace, $sName, $sMode = self::MODE_DEFAULT, $sXMode, $bRoot = false) {

    $iResult = 0;

    if ((!$this->getXMode() || $this->getXMode() === $sXMode) && $sMode === $this->getMode()) {

      if ($bRoot && !$this->getMatch()) {

        $iResult = self::WEIGHT_ELEMENT_ROOT;
      }
      else {

        $iResult = $this->getWeightName($sNamespace, $sName);
      }
    }

    return $iResult;
  }

  public function getWeightSingleName(array $aMatch, $sNamespace, $sName, $iWeight = self::WEIGHT_ELEMENT, $iWeightNS = self::WEIGHT_ELEMENT_NS, $iWeightAll = self::WEIGHT_ELEMENT_ALL) {

    $iResult = 0;
    $bAll = $aMatch['name'] === self::NAME_DEFAULT;

    if ($aMatch['namespace']) {

      if ($sNamespace === $aMatch['namespace']) {

        if ($bAll) {

          $iResult = $iWeightNS;
        }
        else {

          if ($sName === $aMatch['name']) {

            $iResult = $iWeight;
          }
        }
      }
    }
    else {

      if ($bAll) {

        $iResult = $iWeightAll;
      }
    }
    return $iResult;
  }

  public function getWeightName($sNamespace, $sName) {

    $iResult = 0;

    $aNames = $this->getMatch();

    foreach ($aNames as $aName) {

      $iMatch = $this->getWeightSingleName($aName, $sNamespace, $sName);

      if ($iMatch > $iResult) $iResult = $iMatch;
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

    return 'Template ' . ($this->getMatch() ? "({$this->getMatchString()})" : 'root') . ($this->getMode() ? " [mode={$this->getMode()}]" : "");
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

    if (self::CHECK_RECURSION) {

      $aResult[] = $this->getWindow()->toString($this->build());
    }
    else {

      $aResult[] = $this->getWindow()->parseArrayables($this->build());
    }

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

    //$this->build();

    $this->bCloned = true;
/*
    $aContent = array();

    foreach ($this->aContent as $item) {

      if ($item instanceof template\parser\clonable) {

        $aContent[] = clone $item;
      }
    }

    $this->aContent = $aContent;
*/
  }

}

