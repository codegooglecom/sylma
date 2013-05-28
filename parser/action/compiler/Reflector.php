<?php

namespace sylma\parser\action\compiler;
use sylma\core, sylma\dom, sylma\parser\action, sylma\parser\languages\common, sylma\parser\languages\php, sylma\parser\reflector as reflector_ns;

class Reflector extends Argumented implements reflector_ns\elemented {

  // Following function are here only for elemented interface compatibility
  // Used when setting action as new parser's parent
  // TODO : remove when refactoring actions

  // STARTBLOCK

  public function parseRoot(dom\element $el) {

    return parent::parseRoot($el);
  }

  public function parseComponent(dom\element $el) {

    return parent::parseComponent($el);
  }

  public function loadComponent($sName, dom\element $el, $manager = null) {

    return parent::loadComponent($sName, $el, $manager);
  }

  public function loadSimpleComponent($sName, $manager = null) {

    return parent::loadSimpleComponent($sName, $manager);
  }

  public function createParser($sNamespace) {

    return parent::createParser($sNamespace);
  }

  public function getNamespace($sPrefix = null) {

    return parent::getNamespace($sPrefix);
  }

  public function getRoot() {

    $this->throwException('Not implemented');
  }

  public function parseFromParent(dom\element $el) {

    $this->throwException('Not implemented');
  }

  public function onFinish() {}

  // ENDBLOCK

  /**
   *
   * @param dom\element $el
   * @return core\argumentable|array|null
   */
  protected function parseElementSelf(dom\element $el) {

    $mResult = null;

    switch ($el->getName()) {

      case 'action' : $mResult = $this->reflectAction($el); break;



      // primitives

      case 'bool' :
      case 'boolean' : $mResult = $this->reflectBoolean($el); break;
      case 'string' :
      case 'text' : $mResult = $this->reflectString($el); break;
      case 'null' : $mResult = $this->reflectNull($el); break;
      case 'array' : $mResult = $this->reflectArray($el); break;
      case 'numeric' : $mResult = $this->reflectNumeric($el); break;


      case 'ns' : $mResult = $this->reflectNS($el); break;
      //case 'argument' :

      break;

      case 'document' : $mResult = $this->reflectDocument($el); break;
      //case 'template' : $mResult = $this->reflectTemplate($el); break;

      // case 'get-settings' :

      case 'context' : $mResult = $this->reflectContext($el); break;
      case 'escape' : $mResult = $this->reflectEscape($el); break;
      case 'function' : $mResult = $this->reflectFunction($el); break;
      case 'script' : $mResult = $this->reflectScript($el); break;

      case 'switch' :
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

        $mResult = parent::parseElementSelf($el);
    }

    return $mResult;
  }

  protected function reflectSettings(dom\element $settings) {

    $aResult = array();

    foreach ($settings->getChildren() as $el) {

      if ($el->getType() == $el::COMMENT) continue;

      $aResult[] = $this->reflectSettingsElement($el);
    }

    return $aResult;
  }

  protected function reflectSettingsElement(dom\element $el) {

    $result = null;

    if ($el->getNamespace() == $this->getNamespace()) {

      switch ($el->getName()) {

        case 'argument' :

          $result = $this->reflectArgument($el);

        break;
        case 'name' :

          //$this->setName($el->read());
        break;

        case 'return' : $this->setReturn($el); break;

        default :

          $result = $this->parseElement($el);
      }
    }
    else {

      $result = $this->parseElement($el);
    }

    return $result;
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

  protected function createActionCall(core\request $path, dom\collection $children) {

    $window = $this->getWindow();

    $aArguments = array(
      'file' => (string) $path->getFile(),
      'arguments' => $path->asArray(),
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

  protected function reflectDocument(dom\element $el) {

    $window = $this->getWindow();
    $first = $el->getFirst();

    if (!$first || $first->getType() === dom\node::TEXT) {

      $this->throwException(sprintf('Invalid children for document, one child element expected in %s, ', $el->asToken()));
    }

    $content = $this->parseElement($first->remove());

    $interface = $window->loadInstance('\sylma\dom\handler');
    $call = $window->createCall($window->getSelf(), 'createDocument', $interface, array($content));

    $mResult = $this->runObject($el, $call->getVar(false));

    return $mResult;
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

  protected function reflectContext(dom\element $el) {

    $sFormat = $this->getFormat();
    $this->setFormat('object');

    $window = $this->getWindow();

    $window->startContext($el->readAttribute('name'));
    $window->add($this->parseChildren($el->getChildren(), true, true));
    $window->stopContext();

    $this->setFormat($sFormat);
  }

  protected function reflectEscape(dom\element $el) {

    if ($el->countChildren() != 1) {

      $this->throwException('Need one and only one argument');
    }

    $window = $this->getWindow();
    $result = $window->createCall($window->addControler(self::ACTION_ALIAS), 'escape', 'php-string', array($this->parse($el->getFirst())));

    return $result;
  }

  protected function reflectFunction(dom\element $el) {

    $sName = $el->readAttribute('name');

    if (!function_exists($sName)) {

      $this->throwException(sprintf('Unknown function : %s', $sName));
    }

    $window = $this->getWindow();
    $aArguments = array();

    foreach ($el->getChildren() as $child) {

      if ($child->getType() != dom\node::ELEMENT) {

        $this->throwException(sprintf('Invalid %s, element expected', $child->asToken()));
      }

      $aArguments[] = $this->parse($child);
    }


    $call = $window->callFunction($sName, $window->argToInstance('php-string'), $aArguments);

    $var = $call->getVar(false);

    if ($this->setVariable($el, $var)) $result = $var;
    else $result = $call;

    return $result;
  }

  protected function reflectScript(dom\element $el) {

    $window = $this->getWindow();
    $path = $this->getControler()->create('path', array($el->readx('@path')));
    $file = $path->asFile();

    //$this->getManager(self::PARSER_MANAGER)->load($file, array(), true, true);

    //$cache = $this->getManager('fs/cache')->getFile((string) $file . '.php');

    return $window->getSelf()->call('includeScript', array((string) $file));
  }
}