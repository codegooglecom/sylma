<?php

namespace sylma\parser\action;
use \sylma\core, \sylma\dom, \sylma\parser, \sylma\storage\fs;

require_once(dirname(__DIR__) . '/Reflector.php');

abstract class Reflector extends parser\Reflector {

  protected function reflectBoolean(dom\element $el) {

    $bAnd = true;

    if (!$mValue = $el->readAttribute('value', null, false)) {

      /*if (($sUse = $el->readAttribute('use', null, false)) && $sUse != 'and') {

        if ($sUse != 'or') {

          $this->throwException(txt('Unknown boolean use defined, "and" or "or" expected in %s', $el->asToken()));
        }

        $bAnd = false;
      }*/

      if ($el->countChildren() != 1) {

        $this->throwException(txt('One children or attribute value expected in %s', $el->asToken()));
      }

      $mValue = $this->parse($el->getFirst());
    }

    return $this->getWindow()->create('boolean', array($this->getWindow(), $mValue));
  }

  protected function reflectArray(dom\element $el) {

    $aResult = array();
    $iKey = 0;

    foreach ($el->getChildren() as $child) {

      if ($child->getType() != dom\node::ELEMENT) {

        $this->throwException(txt('Invalid node type in array : %s', $el->asToken()));
      }

      if (!$mKey = $child->readAttribute('key', $this->getNamespace(), false)) {

        $mKey = $iKey;
        $iKey++;
      }

      if ($child->isElement('item', $this->getNamespace())) {

        $aResult[$mKey] = $this->parse($child->getFirst());
      }
      else {

        $aResult[$mKey] = $this->parse($child);
      }
    }

    return $this->getWindow()->create('array', array($this->getWindow(), $aResult));
  }

  protected function reflectNull(dom\element $el) {

    return $this->getWindow()->create('null', array($this->getWindow()));
  }

  protected function reflectString(dom\element $el) {

    $aResult = array();

    foreach($el->getChildren() as $child) {

      $aResult[] = $this->parse($child);
    }

    return $this->getWindow()->createString($aResult);
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

    return $window->createString($sValue);
  }

  protected function reflectAction(dom\element $el) {

    $result = null;

    $window = $this->getWindow();
    $sPath = $el->readAttribute('path');

    require_once('core/functions/Path.php');
    $sPath = core\functions\path\toAbsolute($sPath, $this->getDirectory());

    $path = $this->getControler()->create('path', array($sPath));

    $aArguments = array(
      'file' => (string) $path->getFile(),
      'arguments' => $path->getArgumentsArray(),
    );

    if ($el->hasChildren()) {

      $iKey = 0;

      foreach ($el->getChildren() as $child) {

        if ($child->getType() != dom\node::ELEMENT) {

          $this->throwException(txt('Invalid %s, element expected', $child->asToken()));
        }

        $sKey = $el->readAttribute('name', $this->getNamespace(), false);

        if (!$sKey) $sKey = $iKey;

        $aArguments['arguments'][$sKey] = $this->parse($child);

        $iKey++;
      }
    }

    $doc = $path->getFile()->getDocument($this->getNS());

    $callAction = $window->createCall($window->getSelf(), 'getActionFile', $window->getSelf()->getInstance(), $aArguments);
    //$window->add($callAction);

    if (!$sReturn = $doc->getx('self:settings/self:return/@format', array(), false)) {

      $sReturn = 'dom';
    }

    switch ($sReturn) {

      case 'txt' :

        $var = $callAction->getVar();
        $result = $window->createCall($var, 'asString', 'php-string');

      break;

      case 'dom' :

        $var = $callAction->getVar();
        $result = $window->createCall($var, 'asDOM', 'php-string');

      break;

      case 'object' :
      break;
      default :

        $this->throwException(txt('Unknown return type : %s', $sReturn));
    }

    return $result;
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

  protected function reflectCall(dom\element $el) {

    $window = $this->getWindow();
    $sMethod = $el->readAttribute('name');

    $method = $this->getInterface()->loadMethod($sMethod);

    return $this->getInterface()->loadCall($window->getSelf(), $method, $el->getChildren());
  }

  protected function reflectDocument(dom\element $el) {

    $window = $this->getWindow();
    $first = $el->getFirst();

    if ($first->getType() === dom\node::TEXT) {

      $this->throwException(txt('Invalid children for document, one child element expected in %s, ', $el->asToken()));
    }

    $content = $this->parseElement($first->remove());

    if ($content instanceof dom\node) {

      $content = $window->create('template', array($window, $content));
    }
    else {

      $content = $window->convertToDom($content);
    }

    $interface = $window->loadInstance('\sylma\dom\handler', '/sylma/dom2/handler.php');
    $call = $window->createCall($window->getSelf(), 'createDocument', $interface, array($content));

    return $this->getInterface()->runCall($call, $el->getChildren());
  }

  protected function reflectNS(dom\element $el) {

    $aResult = array();
    $window = $this->getWindow();

    if (!$sPrefixes = $el->read()) {

      $this->throwException(txt('You must specify comma separated prefixes in content of %s', $el->asToken()));
    }

    foreach (explode(',', $sPrefixes) as $sPrefix) {

      $sPrefix = trim($sPrefix);

      if ($sPrefix{0} == '*') {

        if (!$sNamespace = $el->lookupNamespace(null)) {

          $this->throwException(txt('No default namespace found in @element %s', $el->asToken()));
        }

        $aResult[substr($sPrefix, 1)] = $window->createString($sNamespace);

      } else {

        if (!$sNamespace = $el->lookupNamespace($sPrefix)) {

          $this->throwException(txt('No namespace found with %s in @element %s', $sPrefix, $el->asToken()));
        }

        $aResult[$sPrefix] = $window->createString($sNamespace);
      }
    }

    return $window->argToInstance($aResult);
  }
}