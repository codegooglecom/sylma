<?php

namespace sylma\parser\action\compiler;
use \sylma\core, \sylma\dom, \sylma\storage\fs, \sylma\parser\action, \sylma\parser\languages\common, sylma\parser\languages\php;

require_once('Argumented.php');

require_once('core/functions/Path.php');

class Reflector extends Argumented {

  /**
   *
   * @param dom\element $el
   * @return core\argumentable|array|null
   */
  protected function parseElementSelf(dom\element $el) {

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

      case 'get-variable' : $mResult = $this->reflectGetVariable($el); break;
      case 'ns' : $mResult = $this->reflectNS($el); break;
      //case 'argument' :
      case 'test-argument' :
      case 'get-all-arguments' :
      case 'get-argument' :

        $mResult = $this->reflectGetArgument($el);

      break;

      case 'document' : $mResult = $this->reflectDocument($el); break;
      //case 'template' : $mResult = $this->reflectTemplate($el); break;

      // case 'get-settings' :
      case 'interface' : $mResult = $this->reflectInterface($el); break;
      case 'context' : $mResult = $this->reflectContext($el); break;
      case 'switch' :
      case 'function' :

      case 'xquery' :
      //case 'recall' :
      case 'namespace' :
      case 'php' :
      //case 'special' :
      //case 'controler' :

        //$sName = $el->getAttribute('name');
        //$mResult = $window->setControler($sName);

      //break;

      //case 'redirect' :

        //$mResult = $window->createCall($window->getSelf(), 'getRedirect', 'core\redirect');

      //break;

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

      default :

        $this->throwException(sprintf('Unknown action element : %s', $el->asToken()));
    }

    return $mResult;
  }

  protected function reflectSettings(dom\element $settings) {

    $aResult = array();

    foreach ($settings->getChildren() as $el) {

      if ($el->getType() === $el::TEXT) {

        $this->throwException((sprintf('Invalid %s, element expected', $el->asToken())));
      }

      if ($el->getNamespace() == $this->getNamespace()) {

        switch ($el->getName()) {

          case 'argument' :

            $aResult[] = $this->reflectArgument($el);

          break;
          case 'name' :

            //$this->setName($el->read());
          break;

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

      if (!$attr->getNamespace()) $aArguments[$attr->getName()] = $window->createString($attr->getValue());
    }

    $call = $method->reflectCall($window, $window->getSelf(), $aArguments);
    $var = $call->getVar(false);

    $children = $el->getChildren();
    $aResult = array();

    $this->setVariable($el, $var);

    $aResult = array_merge($aResult, $this->runConditions($var, $children));
    $aResult = array_merge($aResult, $this->runVar($var, $children));

    if (!$aResult) $aResult[] = $call;

    return count($aResult) == 1 ? reset($aResult) : $aResult;
  }

  protected function reflectBoolean(dom\element $el) {

    $bAnd = true;

    if (!$mValue = $el->readAttribute('value', null, false)) {

      /*if (($sUse = $el->readAttribute('use', null, false)) && $sUse != 'and') {

        if ($sUse != 'or') {

          $this->throwException(sprintf('Unknown boolean use defined, "and" or "or" expected in %s', $el->asToken()));
        }

        $bAnd = false;
      }*/

      if ($el->countChildren() != 1) {

        $this->throwException(sprintf('One children or attribute value expected in %s', $el->asToken()));
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

        $this->throwException(sprintf('Invalid node type in array : %s', $el->asToken()));
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

    $result = $this->getWindow()->createString($aResult);

    $var = $this->getWindow()->createVar($result);

    if ($this->setVariable($el, $var)) $result = $var;

    return $result;
  }

  /**
   * @return common\_var
   */
  protected function reflectAction(dom\element $el) {

    $result = null;

    // create path

    $sPath = $el->readAttribute('path');

    $sPath = core\functions\path\toAbsolute($sPath, $this->getDirectory());
    $path = $this->getControler()->create('path', array($sPath, $this->getDirectory()));

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

    $window = $this->getWindow();

    $result = $this->reflectActionReturn($callAction, $sReturn, $sFormat);
    //$window->add($window->createCall($window->getSelf(), 'loadActionContexts', 'php-boolean', array($callAction->getVar())));

    return $result;
  }

  protected function createActionCall(action\path $path, dom\collection $children) {

    $window = $this->getWindow();

    $aArguments = array(
      'file' => (string) $path->getFile(),
      'arguments' => $path->getArgumentsArray(),
    );

    $iKey = 0;

    // load arguments
    foreach ($children as $child) {

      if ($child->getType() != dom\node::ELEMENT) {

        $this->throwException(sprintf('Invalid %s, element expected', $child->asToken()));
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
        $return = $this->getWindow()->createCall($var, 'asString', 'php-string');

        $result = $return->getVar();

      break;

      case 'dom' :

        $var = $call->getVar();
        $return = $this->getWindow()->createCall($var, 'asDOM', 'php-string');

        $result = $return->getVar();

      break;

      case 'object' :

        //if (!$sClass = $el->readAttribute('interface', null, false)) $sClass = 'php-string'; // todo, default class ?
        $sClass = 'php-string';

        $var = $call->getVar();
        $return = $this->getWindow()->createCall($var, 'asObject', $sClass);

        $result = $return->getVar();

      break;

      default :

        $this->throwException(sprintf('Unknown return type for external action : %s', $sReturn));
    }

    return $result;
  }

  protected function reflectCall(dom\element $el) {

    $window = $this->getWindow();
    $sMethod = $el->readAttribute('name');

    $method = $this->getInterface()->loadMethod($sMethod);

    $result = $this->runObject($el, $window->getSelf(), $method);
    //$result = $this->getInterface()->loadCall($window->getSelf(), $method, $el->getChildren());

    return $result;
  }

  protected function reflectDocument(dom\element $el) {

    $window = $this->getWindow();
    $first = $el->getFirst();

    if (!$first || $first->getType() === dom\node::TEXT) {

      $this->throwException(sprintf('Invalid children for document, one child element expected in %s, ', $el->asToken()));
    }

    $content = $this->parseElement($first->remove());

    $interface = $window->loadInstance('\sylma\dom\handler', '/sylma/dom/handler.php');
    $call = $window->createCall($window->getSelf(), 'createDocument', $interface, array($content));

    $mResult = $this->runObject($el, $call->getVar(false));

    return $mResult;
  }

  protected function reflectGetVariable(dom\element $el) {

    $sName = $el->readAttribute('name');

    if (!array_key_exists($sName, $this->aVariables)) {

      $this->throwException(sprintf('Unknown variable : %s', $sName));
    }

    $var = $this->getVariable($sName);

    //if ($var instanceof php\basic\Called) $var = $var->getVar(false);

    $aResult = array();
    $children = $el->getChildren();

    $aResult = array_merge($aResult, $this->runConditions($var, $children));
    $aResult = array_merge($aResult, $this->runVar($var, $children));

    if (!$aResult) $aResult[] = $var;

    return count($aResult) == 1 ? reset($aResult) : $aResult;
  }

  protected function reflectNS(dom\element $el) {

    $aResult = array();
    $window = $this->getWindow();

    if (!$sPrefixes = $el->read()) {

      $this->throwException(sprintf('You must specify comma separated prefixes in content of %s', $el->asToken()));
    }

    foreach (explode(',', $sPrefixes) as $sPrefix) {

      $sPrefix = trim($sPrefix);

      if ($sPrefix{0} == '*') {

        if (!$sNamespace = $el->lookupNamespace(null)) {

          $this->throwException(sprintf('No default namespace found in @element %s', $el->asToken()));
        }

        $aResult[substr($sPrefix, 1)] = $window->createString($sNamespace);

      } else {

        if (!$sNamespace = $el->lookupNamespace($sPrefix)) {

          $this->throwException(sprintf('No namespace found with %s in @element %s', $sPrefix, $el->asToken()));
        }

        $aResult[$sPrefix] = $window->createString($sNamespace);
      }
    }

    return $window->argToInstance($aResult);
  }

  protected function reflectNumeric(dom\element $el) {

    if ($el->countChildren() != 1) {

      $this->throwException(sprintf('Too much children, one child expected in %s', $el->asToken()));
    }

    $content = $this->parseNode($el->getFirst());

    return $this->getWindow()->create('numeric', array($this->getWindow(), $content));
  }

  protected function reflectInterface(dom\element $el) {

    $caller = $this->getControler(self::CALLER_ALIAS);

    $sPath = $el->readAttribute('path');

    $sPath = core\functions\path\toAbsolute($sPath, $this->getDirectory());
    $path = $this->getControler()->create('path', array($sPath, $this->getDirectory(), array(), false));
    $path->setExtensions(array('iml'));
    $path->parse();

    $interface = $caller->getInterface((string) $path->getFile());
    $instance = $interface->getInstance($this->getWindow(), $el->getChildren());

    $var = $this->getWindow()->addVar($instance);

    return $this->runObject($el, $var);
  }

  protected function reflectContext(dom\element $el) {

    $sFormat = $this->getFormat();
    $this->setFormat('object');

    $window = $this->getWindow();

    $window->startContext($el->readAttribute('name'));
    $window->add($this->parseChildren($el->getChildren(), true, true));
    $window->stopContext();

    $this->setFormat($sFormat);
  }
}