<?php

namespace sylma\view\parser\component;
use sylma\core, sylma\dom, sylma\schema as schema_ns, sylma\template as template_ns;

class Container extends template_ns\parser\component\Template {

  const NAME_DEFAULT = '*';
  const MODE_DEFAULT = '*';

  const CONTEXT_ELEMENT = 'element';
  const CONTEXT_TYPE = 'type';

  const CONTEXT_DEFAULT = self::CONTEXT_ELEMENT;

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

    preg_match('`(?:#(\w+)/)?(?:([\w-_]+):)?([\*\w-_]+)`', $sMatch, $aMatches);

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
    if (!$sMode) $sMode = self::MODE_DEFAULT;

    $this->aMatch = array(
      'context' => $sContext,
      'mode' => $sMode,
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

  public function getWeight(schema_ns\parser\element $element, $sContext, $sMode) {

    $iResult = 0;

    if ($this->getMatch()) {

      if (!$sContext) $sContext = self::CONTEXT_DEFAULT;

      if ($sContext === $this->getContext()) {

        if (!$sMode) $sMode = self::MODE_DEFAULT;

        if ($sMode === $this->getMatch('mode')) {

          if (!$iResult = $this->getWeightElement($element)) {

            $iResult = $this->getWeightType($element->getType());
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

        $iResult = 25;
      }
      else if ($this->getMatch('name') === self::NAME_DEFAULT) {

        $iResult = 20;
      }
    }

    return $iResult;
  }

  protected function getWeightType(schema_ns\parser\type $type) {

    $iResult = 0;

    if ($type->getNamespace() === $this->getMatch('namespace')) {

      if ($type->getName() === $this->getMatch('name')) {

        $iResult = 15;
      }
      else if ($this->getMatch('name') === self::NAME_DEFAULT) {

        $iResult = 10;
      }
    }

    return $iResult;
  }
}

