<?php

namespace sylma\view\parser\component;
use sylma\core, sylma\dom, sylma\schema as schema_ns, sylma\template as template_ns;

class Container extends template_ns\parser\component\Template {

  const NAME_DEFAULT = '*';

  const CONTEXT_ELEMENT = 'element';
  const CONTEXT_TYPE = 'type';

  const CONTEXT_DEFAULT = self::CONTEXT_ELEMENT;

  const WEIGHT_ELEMENT = 25;
  const WEIGHT_ELEMENT_ALL = 20;
  const WEIGHT_TYPE = 15;
  const WEIGHT_TYPE_ALL = 10;

  protected $aMatch = array();
  protected $context;

  public function parseRoot(dom\element $el) {

    parent::parseRoot($el);

    $this->parseMatch($el);

    if ($sApply = $el->readx('@apply', array(), false)) {

      $this->setContext($sApply);
    }
    else if ($this->getMatch()) {

      $this->setContext($this->getMatch('context'));
    }
    else {

      $this->setContext(self::CONTEXT_DEFAULT);
    }
  }

  public function parseMatch(dom\element $el) {

    $this->setMatch($el->readx('@match', array(), false));

    if ($this->getMatch()) {

      $this->parseMatchValue($this->getMatch());
    }
  }

  public function parseMatchValue($sMatch) {

    $sMode = $this->getMode();

    if (!$sMatch) {

      $this->throwException('No match defined');
    }

    preg_match('`(?:#(\w+)/)?(?:([\w\-_]+):)?([\*\w\-_]+)`', $sMatch, $aMatches);

    $sContext = $aMatches[1];
    $sPrefix = $aMatches[2];
    $sName = $aMatches[3];

    if (!$sNamespace = $this->lookupNamespace($sPrefix)) {

      $this->launchException('Cannot match value, no namespace defined', get_defined_vars());
    }

    if (!$sName) {

      $this->launchException('Cannot match value, no name defined', get_defined_vars());
    }

    if (!$sContext) $sContext = self::CONTEXT_DEFAULT;
    //if (!$sMode) $sMode = self::MODE_DEFAULT;

    $this->aMatch = array(
      'context' => $sContext,
      //'mode' => $sMode,
      'namespace' => $sNamespace,
      'name' => $sName,
    );
  }

  protected function lookupNamespace($sPrefix) {

    return $this->getParser()->lookupNamespace($sPrefix);
  }

  protected function getContext() {

    return $this->context;
  }

  protected function setContext($context) {

    $this->context = $context;
  }

  public function getMatch($sKey = '') {

    if ($sKey) return $this->aMatch[$sKey];
    else return parent::getMatch();
  }

  public function getWeight(schema_ns\parser\element $element, $sContext, $sMode, $bRoot = false) {

    $iResult = 0;

    if ($this->getMatch() || $bRoot) {

      if (!$sContext) $sContext = self::CONTEXT_DEFAULT;

      if ($sContext === $this->getContext()) {

        //if (!$sMode) $sMode = self::MODE_DEFAULT;

        if ($bRoot) {

          if (!$this->getMatch() && $sMode === $this->getMode()) {

            $iResult = self::WEIGHT_ELEMENT_ALL;
          }
        }
        else if ($sMode === $this->getMode()) {

          if (!$iResult = $this->getWeightElement($element)) {

            if ($type = $element->getType()) {

              $iResult = $this->getWeightType($type);
            }
          }
        }
      }
    }

    return $iResult;
  }

  protected function getWeightElement(schema_ns\parser\element $element) {

    $iResult = 0;

    if ($element->getNamespace() === $this->getMatch('namespace')) {

      if ($element->getName() === $this->getMatch('name')) {

        $iResult = self::WEIGHT_ELEMENT;
      }
      else if ($this->getMatch('name') === self::NAME_DEFAULT) {

        $iResult = self::WEIGHT_ELEMENT_ALL;
      }
    }

    return $iResult;
  }

  protected function startLog($sMessage = 'Template', array $aVars = array()) {

    parent::startLog($sMessage, array(
      'element' => $this->getTree()->getName(),
    ));
  }

  protected function getWeightType(schema_ns\parser\type $type) {

    $iResult = 0;

    if ($type->getNamespace() === $this->getMatch('namespace')) {

      if ($type->getName() === $this->getMatch('name')) {

        $iResult = self::WEIGHT_TYPE;
      }
      else if ($this->getMatch('name') === self::NAME_DEFAULT) {

        $iResult = self::WEIGHT_TYPE_ALL;
      }
    }

    return $iResult;
  }
}

