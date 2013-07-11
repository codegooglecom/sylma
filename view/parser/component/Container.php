<?php

namespace sylma\view\parser\component;
use sylma\core, sylma\dom, sylma\schema as schema_ns, sylma\template as template_ns;

class Container extends template_ns\parser\component\Template {

  const CONTEXT_ELEMENT = 'element';
  const CONTEXT_TYPE = 'type';

  const CONTEXT_DEFAULT = self::CONTEXT_ELEMENT;

  const WEIGHT_TYPE = 20;
  const WEIGHT_TYPE_ALL = 10;

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

    if (!$sMatch) {

      $this->throwException('No match defined');
    }

    preg_match('`(?:#(\w+)/)?(?:([\w\-_]+):)?([\*\w\-_]+)`', $sMatch, $aMatches);

    $sContext = $aMatches[1];
    $sPrefix = $aMatches[2];
    $sName = $aMatches[3];

    if (!$sContext) $sContext = self::CONTEXT_DEFAULT;
    //if (!$sMode) $sMode = self::MODE_DEFAULT;

    $this->aMatch = array(
      'context' => $sContext,
      //'mode' => $sMode,
      //'namespace' => $this->getNamespace(),
      'name' => $sName,
    );
  }

  protected function getContext() {

    return $this->context;
  }

  protected function setContext($context) {

    $this->context = $context;
  }

  public function getWeightSchema(schema_ns\parser\element $element, $sContext, $sMode, $bRoot = false) {

    $iResult = 0;

    if ($this->getMatch() || $bRoot) {

      if (!$sContext) $sContext = self::CONTEXT_DEFAULT;

      if ($sContext === $this->getContext()) {

        //if (!$sMode) $sMode = self::MODE_DEFAULT;

        if (!$this->getMatch()) {

          if ($bRoot && $sMode === $this->getMode()) {

            $iResult = self::WEIGHT_ELEMENT_ROOT;
          }
        }
        else if ($sMode === $this->getMode()) {

          $iElement = $this->getWeightName($element->getNamespace(), $element->getName());

          if ($type = $element->getType()) {

            $iType = $this->getWeightType($type);
            $iResult = $iType > $iElement ? $iType : $iElement;
          }
          else {

            $iResult = $iElement;
          }
        }
      }
    }

    return $iResult;
  }

  protected function startLog($sMessage = 'Template', array $aVars = array()) {

    parent::startLog($sMessage, array_merge($aVars, array(
      'element' => $this->getTree()->asToken(),
    )));
  }

  protected function getWeightType(schema_ns\parser\type $type) {

    $iResult = 0;

    if ($type->getNamespace() === $this->getNamespace()) {

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

