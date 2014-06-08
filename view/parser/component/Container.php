<?php

namespace sylma\view\parser\component;
use sylma\core, sylma\dom, sylma\schema as schema_ns, sylma\template as template_ns;

class Container extends template_ns\parser\template\Argumented {

  const WEIGHT_TYPE = 22;
  const WEIGHT_TYPE_NS = 17;
  const WEIGHT_TYPE_ALL = 12;

  public function parseRoot(dom\element $el) {

    $aResult = parent::parseRoot($el);

    $this->parseMatch($el);

    return $aResult;
  }

  public function parseMatch(dom\element $el) {

    $this->setMatch($el->readx('@match', array(), false));

    if ($this->getMatchString()) {

      $this->parseMatchValue($this->getMatchString(), $el);
    }
  }

  /**
   * @used by self::parseMatchValue() anonymouse closure
   */
  public function buildMatchValue(dom\element $el, $sPrefix, $sName) {

    $sNamespace = '';

    if ($sPrefix and !$sNamespace = $el->lookupNamespace($sPrefix)) {

      $this->launchException('Cannot match value, namespace not found', get_defined_vars());
    }

    if (!$sName) {

      $this->launchException('Cannot match value, no name defined', get_defined_vars());
    }

    return array(
      'name' => $sName,
      'namespace' => $sNamespace,
    );
  }

  public function parseMatchValue($sMatch, dom\element $el) {

    if (!$sMatch) {

      $this->throwException('No match defined');
    }

    preg_match_all('`(?:(?<prefix>[\w\-_]+):)?(?<name>[\*\w\-_]+)[|\s]*`', $sMatch, $aMatches, PREG_SET_ORDER);

    $tpl = $this;
    $aNames = array_map(function($item) use ($el, $tpl) {

      $aResult = $tpl->buildMatchValue($el, $item['prefix'], $item['name']);

      return $aResult;

    }, $aMatches);

    $this->aMatch = $aNames;
  }

  public function getWeightSchema(schema_ns\parser\element $element, $sMode, $sXMode, $bRoot = false) {

    $iResult = 0;

    if ($this->getMatch() || $bRoot) {

      if (!$this->getMatch()) {

        if ($bRoot && $sMode === $this->getMode() && $this->checkXMode($sXMode)) {

          $iResult = self::WEIGHT_ELEMENT_ROOT;

          if ($this->getXMode() && $this->getXMode() === $sXMode) $iResult += 1;
        }
      }
      else if ($sMode === $this->getMode() && $this->checkXMode($sXMode)) {

        $iElement = $this->getWeightName($element->getNamespace(), $element->getName());

        if ($type = $element->getType()) {

          $iType = $this->getWeightType($type);
          $iResult = $iType > $iElement ? $iType : $iElement;

          if ($this->getXMode() && $this->getXMode() === $sXMode) $iResult += 1;
        }
        else {

          $iResult = $iElement;
        }
      }
    }

    return $iResult;
  }

  protected function checkXMode($sXMode) {

    return !$this->getXMode() || $this->getXMode() === $sXMode;
  }

  protected function startLog($sMessage = 'Template', array $aVars = array()) {

    parent::startLog($sMessage, array_merge($aVars, array(
      'element' => $this->getTree()->asToken(),
    )));
  }

  protected function getWeightType(schema_ns\parser\type $type) {

    $iResult = 0;

    if ($this->getMatch()) {

      $aTypes = array_reverse($type->getBases());
      $aTypes[] = $type;

      $aNames = $this->getMatch();

      foreach ($aNames as $aName) {

        foreach ($aTypes as $subtype) {

          $iMatch = $this->getWeightSingleName($aName, $subtype->getNamespace(), $subtype->getName(), self::WEIGHT_TYPE, self::WEIGHT_TYPE_NS, self::WEIGHT_TYPE_ALL);
          if ($iMatch > $iResult) $iResult = $iMatch;
        }
      }
    }

    return $iResult;
  }
}

