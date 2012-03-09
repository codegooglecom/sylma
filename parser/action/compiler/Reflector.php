<?php

namespace sylma\parser\action\compiler;
use \sylma\core, \sylma\dom, \sylma\storage\fs, \sylma\parser\action, \sylma\parser\action\php;

require_once('Argumented.php');

class Reflector extends Argumented {

  /**
   *
   * @param dom\element $el
   * @return core\argumentable|array|null
   */
  protected function parseElementAction(dom\element $el) {

    $mResult = null;

    switch ($el->getName()) {

      case 'action' : $mResult = $this->reflectAction($el); break;

      case 'call' : $mResult = $this->reflectCall($el); break;

      // primitives

      case 'bool' :
      case 'boolean' : $mResult = $this->reflectBoolean($el); break;
      case 'string' :
      case 'text' : $mResult = $this->reflectString($el); break;
      case 'null' : $mResult = $this->reflectNull($el); break;
      case 'array' : $mResult = $this->reflectArray($el); break;
      case 'numeric' : $mResult = $this->reflectNumeric($el); break;

      case 'get-variable' : $mResult = $this->reflectVariable($el); break;
      case 'ns' : $mResult = $this->reflectNS($el); break;
      //case 'argument' :
      case 'test-argument' :
      case 'get-all-arguments' :
      case 'get-argument' :

        $mResult = $this->reflectGetArgument($el);

      break;

      // case 'get-settings' :
      case 'switch' :
      case 'function' :
      case 'interface' :
      break;
      case 'xquery' :
      //case 'recall' :
      case 'namespace' :
      case 'php' :
      //case 'special' :
      //case 'controler' :

        $sName = $el->getAttribute('name');
        $mResult = $window->setControler($sName);

      break;

      case 'redirect' :

        $mResult = $window->createCall($window->getSelf(), 'getRedirect', 'core\redirect');

      break;

  // <object name="window" call="Controler::getWindow()"/>
  // <object name="redirect" call="$oRedirect"/>
  // <object name="user" call="Controler::getUser()"/>
  // <object name="path" call="$oAction-&gt;getPath()" return="true"/>
  // <object name="path-simple" call="$oAction-&gt;getPath()-&gt;getSimplePath()" return="true"/>
  // <object name="path-action" return="true" call="$oAction-&gt;getPath()-&gt;getActionPath()"/>
  // <object name="self" call="$oAction" return="true"/>
  // <object name="directory" call="$oAction-&gt;getPath()-&gt;getDirectory()" return="true"/>
  // <object name="parent-directory" call="$oAction-&gt;getPath()-&gt;getDirectory()-&gt;getParent()" return="true"/>
  // <object name="parent" call="$oAction-&gt;getParent()"/>
  // <object name="database" call="Controler::getDatabase()"/>
      case 'document' :

        //if ($el->hasChildren())

        $mResult = $this->reflectDocument($el);

      break;

      case 'template' :

      default :

        $this->throwException(txt('Unknown action element : %s', $el->asToken()));
    }

    return $mResult;
  }

  protected function reflectSettings(dom\element $settings) {

    $aResult = array();

    foreach ($settings->getChildren() as $el) {

      if ($el->getType() === $el::TEXT) {

        $this->throwException((txt('Invalid %s, element expected', $el->asToken())));
      }

      if ($el->getNamespace() == $this->getNamespace()) {

        switch ($el->getName()) {

          case 'argument' :

            $aResult[] = $this->reflectArgument($el);

          break;
          case 'name' : $this->setName($el->read()); break;

          case 'return' : $this->setReturn($el); break;

          default :

            $aResult[] = $this->parseElement($el);
        }
      }
      else {

        $aResult[] = $this->parseElement($el);
      }
    }

    return $aResult;
  }

  protected function reflectSelfCall(dom\element $el) {

    $window = $this->getWindow();
    $method = $this->getInterface()->loadMethod($el->getName(), 'element');
    $aArguments = array();

    foreach ($el->getAttributes() as $attr) {

      if ($attr->getNamespace()) continue; // skip @le:*
      $aArguments[$attr->getName()] = $this->parseString($attr->getValue());
    }

    $call = $method->reflectCall($window, $window->getSelf(), $aArguments);

    $this->setVariable($el, $call);

    $children = $el->getChildren();

    $aResult = array();

    $aResult = array_merge($aResult, $this->runConditions($call, $children));
    $aResult = array_merge($aResult, $this->runCalls($call, $children));

    if (!$aResult) $aResult[] = $call;

    return count($aResult) == 1 ? reset($aResult) : $aResult;
  }

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

    // create path

    $sPath = $el->readAttribute('path');

    require_once('core/functions/Path.php');
    $sPath = core\functions\path\toAbsolute($sPath, $this->getDirectory());
    $path = $this->getControler()->create('path', array($sPath));

    // create call

    $callAction = $this->createActionCall($path, $el->getChildren());

    // format return

    $doc = $path->getFile()->getDocument($this->getNS());

    $sReturn = 'dom';
    $sFormat = '';

    if ($return = $doc->getx('self:settings/self:return', array(), false)) {

      $sFormat = $return->read();
      $sReturn = $return->readAttribute('format', null, false);
    }

    $result = $this->reflectActionReturn($callAction, $sReturn, $sFormat);

    return $result;
  }

  protected function createActionCall(action\path $path, dom\collection $children) {

    $window = $this->getWindow();

    $aArguments = array(
      'file' => (string) $path->getFile(),
      'arguments' => $path->getArgumentsArray(),
    );

    $iKey = 0;

    foreach ($children as $child) {

      if ($child->getType() != dom\node::ELEMENT) {

        $this->throwException(txt('Invalid %s, element expected', $child->asToken()));
      }

      $sKey = $child->readAttribute('name', $this->getNamespace(), false);

      if (!$sKey) $sKey = $iKey;

      $aArguments['arguments'][$sKey] = $this->parse($child);

      $iKey++;
    }

    return $window->createCall($window->getSelf(), 'getActionFile', $window->getSelf()->getInstance(), $aArguments);
  }

  protected function reflectActionReturn(php\basic\CallMethod $call, $sReturn , $sFormat = '') {

    switch ($sReturn) {

      case 'txt' :

        $var = $call->getVar();
        $result = $this->getWindow()->createCall($var, 'asString', 'php-string');

      break;

      case 'dom' :

        $var = $call->getVar();
        $result = $this->getWindow()->createCall($var, 'asDOM', 'php-string');

      break;

      case 'object' :

        //$this->throwException('todo, format return');

        $result = $call;

      break;

      default :

        $this->throwException(txt('Unknown return type for external action : %s', $sReturn));
    }

    return $result;
  }

  protected function reflectCall(dom\element $el) {

    $window = $this->getWindow();
    $sMethod = $el->readAttribute('name');

    $method = $this->getInterface()->loadMethod($sMethod);

    $result = $this->getInterface()->loadCall($window->getSelf(), $method, $el->getChildren());
    $this->setVariable($el, $result);

    return $result;
  }

  protected function reflectDocument(dom\element $el) {

    $window = $this->getWindow();
    $first = $el->getFirst();

    if (!$first || $first->getType() === dom\node::TEXT) {

      $this->throwException(txt('Invalid children for document, one child element expected in %s, ', $el->asToken()));
    }

    $content = $this->parseElement($first->remove());

    $interface = $window->loadInstance('\sylma\dom\handler', '/sylma/dom2/handler.php');
    $call = $window->createCall($window->getSelf(), 'createDocument', $interface, array($content));

    $this->setVariable($el, $call);
    $aCalls = $this->runCalls($call, $el->getChildren());

    return $aCalls ? $aCalls : $call;
  }

  protected function reflectVariable(dom\element $el) {

    $sName = $el->readAttribute('name');

    if (!array_key_exists($sName, $this->aVariables)) {

      $this->throwException(txt('Unknown variable : %s', $sName));
    }

    return $this->aVariables[$sName];
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

  protected function reflectNumeric(dom\element $el) {

    if ($el->countChildren() != 1) {

      $this->throwException(txt('Too much children, one child expected in %s', $el->asToken()));
    }

    $content = $this->parseNode($el->getFirst());

    return $this->getWindow()->create('numeric', array($this->getWindow(), $content));
  }
}