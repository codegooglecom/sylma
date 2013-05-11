<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common, sylma\template\parser;

class Template extends Child implements common\arrayable, parser\template, core\tokenable {

  const MATCH_DEFAULT = '[root]';
  const MODE_DEFAULT = '';

  const CHECK_RECURSION = false; // if TRUE, disable concat optimization

  protected $aContent;
  protected $aComponents = array();

  protected $bBuilded = false;
  protected $sMatch;

  protected $tree;
  protected $pather;

  protected $bCloned = false;
  protected static $aCall = array();
  protected $aVariables = array();
  protected $sID = '';
  protected $sMode = self::MODE_DEFAULT;

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->loadMode();

    $this->allowUnknown(true);
    $this->allowForeign(true);
    $this->allowComponent(true);
    $this->allowText(true);

    $this->setID(uniqid());
    $this->build();
  }

  protected function setID($sID) {

    if ($this->sID) {

      $this->launchException('Template already IDed');
    }

    $this->sID = $sID;
  }

  protected function getID() {

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

  protected function loadAttributes(dom\element $el, Element $component) {

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

  public function getMatch() {

    return $this->sMatch;
  }

  public function setTree(parser\tree $tree) {

    if (!$this->bCloned && $this->getMatch()) {

      $this->launchException('Template must be cloned');
    }

    if ($this->tree) {

      $this->launchException('Tree ever assigned');
    }

    //$this->initComponents();

    $this->tree = $tree;
  }

  public function getTree() {

    if (!$this->tree) {

      $this->launchException('No tree defined');
    }

    return $this->tree;
  }

  protected function parseElementSelf(dom\element $el) {

    switch ($el->getName()) {

      case 'use' : $result = $this->reflectUse($el); break;
      default :

        $result = parent::parseElementSelf($el);
    }

    return $result;
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

      $mContent = $this->parseComponentRoot($this->getNode());

      $this->stop();

      $this->aContent = is_array($mContent) ? $mContent : array($mContent);
      $this->bBuilded = true;
    }

    return $this->aContent;
  }

  public function setVariable(Variable $var) {

    $this->aVariables[$var->getName()] = $var;
  }

  public function getVariable($sName) {

    return $this->aVariables[$sName];
  }

  protected function initComponents() {

    foreach ($this->aComponents as $component) {

      $component->setTemplate($this);
    }
  }

  protected function addComponent(parser\component $sub) {

    $sub->setTemplate($this); // first set for component build

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

    if (in_array($this->getID(), self::$aCall)) {

      $this->launchException('Recursive template call');
    }

    self::$aCall[] = $this->getID();

    $this->start();
    $this->startLog();

    if (self::CHECK_RECURSION) $result = array($this->getWindow()->toString($this->build()));
    else $result = $this->getWindow()->parseArrayables($this->build());

    $this->stopLog();
    $this->stop();

    array_pop(self::$aCall);

    return $result;
  }

  protected function startLog($sMessage = '', array $aVars = array()) {

    parent::startLog(
      $this->asToken(),
      array_merge(array(), $aVars)
    );
  }

  public function getPather() {

    //if (!$this->pather) {

      $pather = $this->loadSimpleComponent('pather');

      $pather->setSource($this->getTree());
      $pather->setTemplate($this);
    //}

    return $pather;
  }

  public function applyPath($sPath, $sMode) {

    $pather = $this->getPather();

    return $sPath ? $pather->applyPath($sPath, $sMode) : $this->getTree()->reflectApply($sMode);
  }

  public function __clone() {

    $this->bCloned = true;
  }

  public function asArgument() {

    $this->launchException('Should not be called');

    return $this->getParser()->addToResult($this->asArray(), false);
  }

  protected function launchException($sMessage, array $aVars = array(), array $mSender = array()) {

    $mSender[] = $this->getNode()->asToken() . ' @match ' . $this->getMatch();
    $aVars[] = $this->getNode();

    parent::launchException($sMessage, $aVars, $mSender);
  }

  public function asToken() {

    return 'Template ' . ($this->getMatch() ? "({$this->getMatch()})" : 'root') . ($this->getMode() ? " [mode={$this->getMode()}]" : "");
  }
}

