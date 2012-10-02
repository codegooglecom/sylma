<?php

/**
* This file is part of the PHP framework Sylma : http://www.sylma.org
*
* @copyright 2012 Rodolphe Gerber [rodolphe.gerber@sylma.org]
* @licence http://www.gnu.org/licenses/gpl.html General Public Licence version 3
*/

namespace sylma\parser\action\compiler;
use sylma\core, sylma\dom, sylma\parser, sylma\parser\languages\common, sylma\parser\languages\php;

require_once('Runner.php');
require_once('parser/reflector/documented.php');

abstract class Domed extends Runner implements parser\reflector\documented {

  protected $currentElement;

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

    $sFormat = $this->getFormat();

    $this->setFormat('object');
    $contexts = $doc->queryx('self:context', array(), false);

    foreach ($contexts as $context) {

      $this->reflectContext($context);
      $context->remove();
    }

    $this->setFormat($sFormat);
    //$this->getWindow()->startContext(common\_window::CONTEXT_DEFAULT);

    $children = $doc->getChildren();
    $children->setIndex(count($aResults));

    $aResults[common\_window::CONTEXT_DEFAULT] = $this->parseChildren($children, true);

    return $aResults;
  }

  /**
   *
   * @param dom\element $el
   * @return dom\node|array|null
   */
  protected function parseElementForeign(dom\element $el) {

    $mResult = null;
    //$parent = $this->getControler()->create('document');

    if ($this->getInterface()->useElement() && $el->getNamespace() == $this->getNamespace('class')) {

      $mResult = $this->reflectSelfCall($el);
    }
    else {

      $mResult = parent::parseElementForeign($el);
    }

    return $mResult;
  }

  protected function parseElementUnknown(dom\element $el) {

    $this->useTemplate(true);

    $newElement = $this->createElement($el->getName(), null, array(), $el->getNamespace());

    if ($this->useForeignAttributes($el)) {

      $mResult = $this->parseAttributesForeign($el, $newElement);
    }
    else {

      foreach ($el->getAttributes() as $attr) {

        $newElement->add($this->parseAttribute($attr));
      }

      $mResult = $newElement;
    }

    if ($aChildren = $this->parseChildren($el->getChildren())) {

      $newElement->add($aChildren);
    }

    return $mResult;
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

            $mResult = $this->parseElement($child);

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
          catch (core\exception $e) {

            $e->addPath($child->asToken());
            throw $e;
          }

        break;

        case $child::TEXT :

          if ($bContext) {

            $mResult = $this->getWindow()->createInsert($this->getWindow()->argToInstance($child->getValue()), 'txt');
          }
          else {

            $aResult[] = $child;
          }

        break;

        default :

          $aResult[] = $child;
      }

      $children->next();
    }

    return $aResult;
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

        switch ($aResult['typ'][0]) {

          case 'call' :

            $aArguments = array();

            $method = $this->getInterface()->loadMethod($aResult['val'][0]);
            $arg = $method->reflectCall($window, $window->getSelf(), $aArguments);

          break;

          case 'arg' :

            $arg = $this->getActionArgument($aResult['val'][0]);

          break;

          default :

            $this->throwException(sprintf('unknown attribute call : %s', $aResult['typ']));

        }

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

}
