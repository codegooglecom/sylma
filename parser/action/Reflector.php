<?php

namespace sylma\parser\action;
use \sylma\core, \sylma\dom, \sylma\parser, \sylma\storage\fs;

require_once(dirname(__DIR__) . '/Reflector.php');

abstract class Reflector extends parser\Reflector {

  protected function reflectBoolean(dom\element $el) {

    $bAnd = true;

    if (!$mValue = $el->readAttribute('value', null, false)) {

      if (($sUse = $el->readAttribute('use')) && $sUse != 'and') {

        if ($sUse != 'or') {

          $this->throwException(txt('Unknown boolean use defined, "and" or "or" expected in %s', $el->asToken()));
        }

        $bAnd = false;
      }

      if (!$el->getChildren()) {

        $this->throwException(txt('No value defined, children or attribute value expected in %s', $el->asToken()));
      }

      $aResult = array();

      foreach ($el->getChildren() as $child) {

        $aResult[] = $this->parse($child);
      }

      $mValue = !$bAnd;

      foreach ($aResult as $mVal) {

        if (!$mVal && $bAnd) break;
        else if (!$bAnd) break;
      }

      $mValue = $mVal;
    }

    return $this->getWindow()->create('boolean', array($this->getWindow(), $mValue));
  }

  protected function reflectString(dom\element $el) {

    $aResult = array();

    foreach($el->getChildren() as $child) {

      $aResult[] = $this->parse($child);
    }

    return $this->getWindow()->create('concat', array($this->getWindow(), $aResult));
  }

  protected function reflectText(dom\element $el) {

    return $this->getWindow()->create('string', array((string) $el));
  }

  protected function parseString($sValue) {

    $window = $this->getWindow();

    preg_match_all('/\[\$([\w-]+)\]/', $sValue, $aResults, PREG_OFFSET_CAPTURE);

    if ($aResults && $aResults[0]) {

      $iSeek = 0;

      foreach ($aResults[1] as $aResult) {

        $iVarLength = strlen($aResult[0]) + 3;
        $sVarValue = (string) $window->getVariable($aResult[0]);

        $sValue = substr($sValue, 0, $aResult[1] + $iSeek - 2) . $sVarValue . substr($sValue, $aResult[1] + $iSeek - 2 + $iVarLength);

        $iSeek += strlen($sVarValue) - $iVarLength;
      }
    }

    return $window->create('string', array($sValue));
  }

  protected function reflectAction(dom\element $el) {

    $sPath = $el->getAttribute('path');

    $parser = $this->getControler();

    //arguments

    return $parser->createAction($sPath);
  }

  protected function reflectGetArgument(dom\element $el) {

    $window = $this->getWindow();
    $sName = $el->getAttribute('name');

    if (!$mVal = $this->getArgument($sName)) {

      $this->throwException(txt('Unknown argument : %s', $sName));
    }

    return $window->createCall($window->getSelf(), 'getArgument', $window->loadInstance($mVal), array($sName));
  }

  protected function reflectSettingsArgument(dom\element $el) {

    $aResult = array();
    $window = $this->getWindow();

    $sName = $el->getAttribute('name');
    $sFormat = $el->getAttribute('format');

    $val = $window->stringToInstance($sFormat);

    if ($val instanceof php\basic\StringInstance) {

      $call = $window->createCall($window->getSelf(), 'readArgument', $val, array($sName));
      $aResult[] = $window->createCall($window->getSelf(), 'validateString', 'boolean', array($call));
    }
    else if ($val instanceof php\basic\ArrayInstance) {

      $call = $window->createCall($window->getSelf(), 'getArgument', $val, array($sName));
      $aResult[] = $window->createCall($window->getSelf(), 'validateString', 'boolean', array($call));
    }
    else if ($val instanceof php\_object) {

      $call = $window->createCall($window->getSelf(), 'getArgument', $val, array($sName));

      $interface = $val->getInterface();
      $aResult[] = $window->createCall($window->getSelf(), 'validateObject', 'boolean', array($call, $interface->getName()));
    }

    if ($el->hasChildren()) {

      if ($validate = $el->get('self:validate')) {


      }
    }

    return $aResult;
  }
}