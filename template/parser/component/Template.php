<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\template as template_ns;

class Template extends Child implements common\arrayable, template_ns\parser\template, core\tokenable {

  const NAME_DEFAULT = '*';

  const WEIGHT_ELEMENT = 25;
  const WEIGHT_ELEMENT_ALL = 15;
  const WEIGHT_ELEMENT_ROOT = 25;

  const MATCH_DEFAULT = '[root]';
  const MODE_DEFAULT = '';

  const CHECK_RECURSION = false; // if TRUE, disable concat optimization

  protected $aContent;
  protected $aComponents = array();

  protected $bBuilded = false;

  protected $sMatch;
  protected $aMatch = array();

  protected $tree;
  protected $pather;

  protected $bCloned = false;
  protected static $aCall = array();

  protected $aHeaders = array();

  protected $aParameters = array();
  protected $aVariables = array();

  protected $sID = '';
  protected $sMode = self::MODE_DEFAULT;
  protected $aSendParameters = array(); // arguments are stored until template is ready

  public function parseRoot(dom\element $el) {

    $this->setNode($el, true, false);
    $this->parseMatchNamespace($el);

    $this->loadMode();

    $this->allowUnknown(true);
    $this->allowForeign(true);
    $this->allowComponent(true);
    $this->allowText(true);

    $this->setID(uniqid());
    $this->build();
  }

  protected function parseMatchNamespace(dom\element $el) {

    if ($sMatch = $el->readx('@match', array(), false)) {

      preg_match_all('/(\w+):(\w+|\*)/', $sMatch, $aMatches, PREG_SET_ORDER);

      foreach ($aMatches as $aMatch) {

        list(,$sPrefix, $sName) = $aMatch;

        if (!$sNamespace = $el->lookupNamespace($sPrefix)) {

          $this->launchException('Cannot match value, no namespace defined', get_defined_vars());
        }

        if (!$sName) {

          $this->launchException('Cannot match value, no name defined', get_defined_vars());
        }

        $this->setNamespace($sNamespace);
      }
    }
  }

  protected function setID($sID) {

    $this->sID = $sID;
  }

  public function getID() {

    return $this->sID;
  }

  public function loadElement(dom\element $el) {

    return $this->loadElementUnknown($el);
  }

  protected function loadElementUnknown(dom\element $el) {

    $element = $this->loadSimpleComponent('element');
    $element->setTemplate($this);
    $this->addComponent($element);

    $result = $this->loadAttributes($el, $element);
    $element->parseRoot($el);

    //$element->build();

    return $result;
  }

  public function parseComponent(dom\element $el) {

    $result = parent::parseComponent($el);
    $this->addComponent($result);

    return $result;
  }

  protected function loadAttributes(dom\element $el, template_ns\element $component) {

    if ($this->useForeignAttributes($el)) {

      $aForeigns = $this->getForeignAttributes($el, null);
      $mResult = $this->parseAttributesForeign($el, $component, $aForeigns);
      $this->getForeignAttributes($el, null, true);
    }
    else {

      $mResult = $component;
    }

    $aParsers = $this->getAttributeParsers();
    $this->setAttributeParsers();

    foreach ($aParsers as $parser) {

      $parser->onClose($el, $mResult);
    }

    return $mResult;
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

  public function parseArguments(dom\collection $children) {

    $aResult = array();

    foreach ($children as $child) {

      $aResult[$this->parseElementKey($child)] = $this->parseElement($child);
    }

    return $aResult;
  }

  protected function setParameter($sName, Argument $arg) {

    $this->aParameters[$sName] = $arg;
  }

  protected function getParameters() {

    return $this->aParameters;
  }

  public function sendArguments(array $aVars) {

    $this->aSendParameters = $aVars;
  }

  protected function checkArguments() {

    $aVars = $this->aSendParameters;

    foreach ($this->getParameters() as $sName => $arg) {

      $content = isset($aVars[$sName]) ? $aVars[$sName] : $arg->getDefault();

      $var = clone $arg;

      $this->aVariables[$sName] = $var;
      $this->aHeaders[] = $var->setContent($content);
    }
  }

  public function setVariable(Variable $var) {

    $this->aVariables[$var->getName()] = $var;
  }

  public function getVariable($sName) {

    if (!isset($this->aVariables[$sName])) {

      $this->launchException("Variable '{$sName}' does not exists");
    }

    return $this->aVariables[$sName];
  }

  protected function initComponents() {

    foreach ($this->aComponents as $component) {

      $component->setTemplate($this);
    }
  }

  protected function addComponent(template_ns\parser\component $sub) {

    $sub->setTemplate($this); // first set for component build

    if ($sub instanceof Argument) {

      $this->setParameter($sub->getName(), $sub);
    }

    $this->aComponents[] = $sub;
  }

  public function isCloned() {

    return $this->bCloned;
  }

  public function asArray() {

    if (!$this->isCloned() && $this->getMatch()) {

      $this->launchException('Template must be cloned');
    }

    $this->getTree(); // exists
    $this->initComponents();
    $this->checkArguments();

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

  protected function startLog($sMessage = '', array $aVars = array()) {

    parent::startLog($this->asToken(), array_merge(array('file' => (string) $this->getSourceFile()), $aVars));
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

  public function useOnce() {

    return $this->readx('@once');
  }

  public function getPather() {

    //if (!$this->pather) {

      $pather = $this->loadSimpleComponent('pather');

      $pather->setSource($this->getTree());
      $pather->setTemplate($this);
    //}

    return $pather;
  }

  public function readPath($sPath, $sMode, array $aArguments = array()) {

    $pather = $this->getPather();

    return $pather->readPath($sPath, $sMode, $aArguments);
  }

  public function applyPath($sPath, $sMode, array $aArguments = array()) {

    $pather = $this->getPather();

    return $pather->applyPath($sPath, $sMode, $aArguments);
  }

  public function parseValue($sValue) {

    preg_match_all('/{([^}]+)}/', $sValue, $aMatches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

    if ($aMatches) {

      $mResult = array();
      $iOffset = 0;

      foreach ($aMatches as $i => $aResult) {

        $iStart = $aResult[0][1];

        $iVarLength = mb_strlen($aResult[0][0]);
        $val = $this->applyPath($aResult[1][0], '');

        $iDiff = $iStart - $iOffset;

        $sStart = mb_substr($sValue, $iOffset, $iDiff);

        if ($i == (count($aMatches) - 1)) {

          $mResult[] = array($sStart, $val, mb_substr($sValue, $iStart + $iVarLength));
        }
        else {

          $mResult[] = array($sStart, $val);
          $iOffset += $iDiff + $iVarLength;
        }
      }
    }
    else {

      $mResult = $sValue;
    }

    return $mResult;
  }

  public function __clone() {

    $this->bCloned = true;
  }

  public function asArgument() {

    $this->launchException('Should not be called');

    return $this->getParser()->addToResult($this->asArray(), false);
  }

  protected function launchException($sMessage, array $aVars = array(), array $mSender = array()) {

    $mSender[] = ($this->getNode(false) ? $this->getNode()->asToken() : '[no-node]') . ' @match ' . $this->getMatch();
    $aVars[] = $this->getNode(false);

    parent::launchException($sMessage, $aVars, $mSender);
  }

  public function asToken() {

    return 'Template ' . ($this->getMatch() ? "({$this->getMatch()})" : 'root') . ($this->getMode() ? " [mode={$this->getMode()}]" : "");
  }
}

