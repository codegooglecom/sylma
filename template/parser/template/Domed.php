<?php

namespace sylma\template\parser\template;
use sylma\core, sylma\dom, sylma\template as template_ns;

class Domed extends Basic {

  protected $aComponents = array();

  protected static $aCall = array();

  protected $aHeaders = array();

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

  public function parseComponent(dom\element $el) {

    $result = parent::parseComponent($el);
    $this->addComponent($result);

    return $result;
  }

  protected function initComponents() {

    foreach ($this->aComponents as $component) {

      $component->setTemplate($this);
    }
  }

  protected function addComponent(template_ns\parser\component $sub) {

    $sub->setTemplate($this); // first set for component build

    $this->aComponents[] = $sub;
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

  protected function launchException($sMessage, array $aVars = array(), array $mSender = array()) {

    $mSender[] = ($this->getNode(false) ? $this->getNode()->asToken() : '[no-node]') . ' @match ' . $this->getMatch();
    $aVars[] = $this->getNode(false);

    parent::launchException($sMessage, $aVars, $mSender);
  }

  protected function initRender() {

    parent::initRender();
    $this->initComponents();
  }
}

