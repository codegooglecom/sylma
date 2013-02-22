<?php

namespace sylma\parser\action\compiler;
use sylma\core, sylma\dom, sylma\parser\languages\common;

abstract class Domed extends Main {

  protected $currentElement;

  protected $allowForeign = true;
  protected $allowUnknown = true;

  protected function parseDocument(dom\document $doc) {

    $aResults = array();

    if ($doc->isEmpty()) {

      $this->throwException(t('empty doc'));
    }

    $doc->registerNamespaces($this->getNS());

    $this->setFormat('dom');
    $settings = $doc->getx('self:settings', array(), false);

    if ($settings) {

      $this->getWindow()->add($this->reflectSettings($settings));
      $settings->remove();
    }
/*
    $sFormat = $this->getFormat();

    $this->setFormat('object');
    $contexts = $doc->queryx('self:context', array(), false);

    foreach ($contexts as $context) {

      $this->reflectContext($context);
      $context->remove();
    }

    $this->setFormat($sFormat);
 */
    //$this->getWindow()->startContext(common\_window::CONTEXT_DEFAULT);

    $children = $doc->getChildren();
    $children->setIndex(count($aResults));

    $aResults[common\_window::CONTEXT_DEFAULT] = $this->parseChildren($children, true);

    return $aResults;
  }

  protected function parseElementSelf(dom\element $el) {

    $this->throwException(sprintf('Unknown action element : %s', $el->asToken()));
  }

  protected function parseElementUnknown(dom\element $el) {

    $this->useTemplate(true);

    return parent::parseElementUnknown($el);
  }

  /**
   * Parse children into main context. Insert results
   * @param dom\element $el
   * @return array
   */
  protected function parseChildren(dom\collection $children, $bRoot = false, $bContext = false) {

    $aResult = array();

    while ($child = $children->current()) {

      switch ($child->getType()) {

        case $child::ELEMENT :

          try {

            $this->parseChildrenElement($child, $aResult, $bRoot);
          }
          catch (core\exception $e) {

            $e->addPath($child->asToken());
            throw $e;
          }

          break;

        case $child::TEXT :

          $this->parseChildrenText($child, $aResult, $bContext);

          break;

        default :

          $aResult[] = $child;
      }

      $children->next();
    }

    return $aResult;
  }

  protected function parseChildrenElement(dom\element $el, array &$aResult, $bRoot = false) {

    $mResult = $this->parseElement($el);

    if ($mResult) {

      if (!$mResult instanceof dom\node && !$mResult instanceof common\structure) {

        if (is_array($mResult)) {

          $mResult = $this->getWindow()->argToInstance($mResult);
        }

        $bTemplate = !($this->getWindow()->getContext());

        $mResult = $this->getWindow()->createInsert($mResult, $this->getFormat(), null, $bTemplate, $bRoot);
      }

      $aResult[] = $mResult;
    }
  }

  protected function loadElementForeign(dom\element $el) {

    if ($parser = $this->lookupParserForeign($el->getNamespace())) {

      $mResult = $parser->parseRoot($el);
    }
    else {

      $mResult = $this->parseElementUnknown($el);
    }

    return $mResult;
  }

  protected function parseChildrenText(dom\text $node, array &$aResult, $bContext = false) {

    if ($bContext) {

      $mResult = $this->getWindow()->createInsert($this->getWindow()->argToInstance($node->getValue()), 'txt');
    }
    else {

      $mResult = $node;
    }

    $aResult[] = $mResult;
  }

  protected function parseAttribute(dom\attribute $attr) {

    $attr->setValue($this->parseString($attr->getValue()));
    return $attr;
  }

  protected function parseString($sValue) {

    $window = $this->getWindow();

    preg_match_all('/\[sylma:(?P<typ>[\w-]+)(?:::(?P<val>[\w-]+))?\]/', $sValue, $aResults, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
    //dspf($aResults);

    if ($aResults) {

      $iSeek = 0;

      foreach ($aResults as $aResult) {

        $iVarLength = strlen($aResult[0][0]);

        $arg = $this->parseStringCall($aResult['typ'][0], $aResult['val'][0]);

        $insert = $window->createInsert($arg);
        $sVarValue = $insert->asString();

        $sStart = substr($sValue, 0, $aResult[0][1] + $iSeek);
        $sEnd = substr($sValue, $aResult[0][1] + $iSeek + $iVarLength);

        $sValue = $sStart . $sVarValue . $sEnd;

        $iSeek += strlen($sVarValue) - $iVarLength;
      }
    }
    //dspf($sValue);
    return $sValue;
  }

  protected function parseStringCall($sName, $sValue) {

    switch ($sName) {

      case 'variable' :

        $result = $this->getVariable($sValue);

      break;

      default :

        $this->throwException(sprintf('unknown attribute call : %s', $sName));

    }

    return $result;
  }
}
