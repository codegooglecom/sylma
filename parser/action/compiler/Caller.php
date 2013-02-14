<?php

namespace sylma\parser\action\compiler;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\parser\languages\php, sylma\parser\action, sylma\parser\reflector, sylma\core\functions\text;

\Sylma::load('/core/functions/Text.php');

class Caller extends Domed implements action\reflector {

  const CALLSELF_PREFIX = 'class';

  protected $aCallShorts = array();

  protected function build() {

    $this->loadCallShorts();

    return parent::build();
  }

  protected function parseElementSelf(dom\element $el) {

    $mResult = null;

    if ($el->getNamespace() == $this->getNamespace('class')) {

      $mResult = $this->reflectCallShort($el);
    }
    else {

      switch ($el->getName()) {

        case 'call-self' : $mResult = $this->reflectCallSelf($el); break;
        case 'call' : $mResult = $this->reflectCallMethod($el); break;
        case 'object' : $mResult = $this->reflectObject($el); break;

        default :

          $mResult = parent::parseElementSelf($el);
      }
    }

    return $mResult;
  }

  protected function parseStringCall($sName, $sValue) {

    switch ($sName) {

      case 'call' :

        $aArguments = array();
        $window = $this->getWindow();

        $method = $this->getInterface()->loadMethod(text\toggleCamel($sValue));
        $result = $method->reflectCall($window, $window->getSelf(), $aArguments);

      break;

      default :

        $result = parent::parseStringCall($sName, $sValue);
    }

    return $result;
  }

  /**
   * Shortcut
   * @return php\basic\_Interface
   */
  protected function getInterface() {

    return $this->getWindow()->getSelf()->getInterface();
  }

  /**
   *
   * @param common\_var $var
   * @param dom\collection $children
   * @return array|\sylma\parser\languages\common\_var
   */
  protected function runVar(common\_var $var, dom\collection $children) {

    $aResult = array();

    if ($children->current()) {

      $var->insert();

      $window = $this->getWindow();
      $window->setObject($var);

      $interface = $this->loadObjectInterface($var);

      while ($child = $children->current()) {

        $children->next();
        $call = $this->parseCall($interface, $child, $var);

        if ($sub = $this->setVariable($child, $call)) {

          $aResult[] = $sub;
        }
        else {

          $aResult[] = $call;
        }
      }

//      if (count($aResult) == 1) $mResult = $aResult[0];
//      else $mResult = $aResult;

      $window->stopObject();
    }

    return $aResult;
  }


  /**
   *
   * @param php\basic\CallMethod $call
   * @param dom\collection $children
   * @return array
   */
  protected function runConditions(common\_var $call, dom\collection $children) {

    $aResult = array();

    while (($child = $children->current()) && $child->getType() == dom\node::ELEMENT) {

      if ($child->getNamespace() == $this->getNamespace() && in_array($child->getName(), array('if', 'if-not'))) {

        // from here, condition can be builded

        $sName = $child->getName();
        $window = $this->getWindow();

        if ($child->getChildren()->length != 1) {

          $this->throwException(sprintf('Invalid children, one child expected in %s', $child->asToken()));
        }

        $content = $this->parse($child->getFirst());
        $var = $window->createVar($content);

        $var->insert($window->tokenToInstance('php-null'));
        //$window->add($window->createAssign($var, )));
        $assign = $window->createAssign($var, $content);

        $call->insert();

        if ($sName == 'if') {

          $condition = $window->createCondition($call, $assign);
        }
        else { // if ($sName == 'if-not') {

          $not = $window->createNot($call);
          $condition = $window->createCondition($not, $assign);
        }

        $window->add($condition);
        $aResult[] = $var;
      }
      else {

        break;
      }

      $children->next();
    }

    return $aResult;
  }

  protected function runObject(dom\element $el, php\basic\_ObjectVar $var, php\basic\Method $method = null) {

    $children = $el->getChildren();

    if ($method) {

      $interface = $var->getInstance()->getInterface();
      $call = $interface->loadCall($var, $method, $children);
      $resultVar = $call->getVar(false);
    }
    else {

      $resultVar = $var;
    }

    $this->setVariable($el, $resultVar);

    $aResult = array();

    $aResult = array_merge($aResult, $this->runConditions($resultVar, $children));
    $aResult = array_merge($aResult, $this->runVar($resultVar, $children));

    // Child result returned by default, else parent result is returned
    if (!$aResult) {

      $aResult[] = $resultVar;
      $resultVar->insert();
    }

    return count($aResult) == 1 ? reset($aResult) : $aResult;
  }

  /**
   * Find interface corresponding to object given as argument
   * @param common\_object $obj
   * @return parser\caller
   */
  protected function loadObjectInterface(common\_object $obj) {

    $result = $obj->getInstance()->getInterface();

    return $result;
  }

  protected function loadCallShorts() {

    if ($elements = $this->getManager()->getArgument('cache/elements', false)) {

      $sNamespace = $this->getManager()->readArgument('cache/namespace');

      $this->setNamespace($sNamespace, static::CALLSELF_PREFIX, false);
      $this->setUsedNamespace($sNamespace);

      foreach ($elements as $sName => $sMethod) {

        $this->aCallShorts[$sName] = $sMethod; //$this->toggleMethod($sMethod, false);
      }
    }
  }

  protected function getCallShort($sName) {

    if (!array_key_exists($sName, $this->aCallShorts)) {

      $this->throwException(sprintf('Unknown short call : %s', $sName));
    }

    return $this->aCallShorts[$sName];
  }

  public function parseCall(php\basic\_Interface $interface, dom\element $el, php\basic\_ObjectVar $var) {

    $sMethod = $el->readAttribute('name');

    return $this->runObject($el, $var, $interface->getMethod(text\toggleCamel($sMethod)));
  }

  protected function reflectObject(dom\element $el) {

    $sName = $el->readAttribute('class');
    $sFile = $el->readAttribute('file', null, false);

    $interface = $this->getWindow()->getInterface($this->getAbsoluteClass($sName));
    $instance = $interface->addInstance($this->getWindow(), $el->getChildren());

    $var = $this->getWindow()->addVar($instance);

    return $this->runObject($el, $var);
  }

  protected function getAbsoluteClass($sPath) {

    if ($sPath{0} == '\\') {

      $sResult = $sPath;
    }
    else {

      $aPaths = explode('\\', $sPath);
      $aDirectories = explode('/', (string) $this->getDirectory());

      foreach ($aPaths as $sPath) {

        if ($sPath == '..') {

          array_shift($aPaths);
          array_pop($aDirectories);
        }
        else {

          break;
        }
      }

      \Sylma::load('/core/functions/Path.php');
      $sResult = implode(array_merge($aDirectories, $aPaths), '\\');
    }

    return $sResult;
  }

  protected function reflectCallMethod(dom\element $el) {

    $window = $this->getWindow();
    $interface = $this->loadObjectInterface($window->getObject());

    return $this->parseCall($interface, $el, $window->getObject());
  }

  protected function reflectCallSelf(dom\element $el) {

    $window = $this->getWindow();
    $sMethod = $el->readAttribute('name');

    $method = $this->getInterface()->loadMethod(text\toggleCamel($sMethod));

    $result = $this->runObject($el, $window->getSelf(), $method);
    //$result = $this->getInterface()->loadCall($window->getSelf(), $method, $el->getChildren());

    return $result;
  }

  protected function reflectCallShort(dom\element $el) {

    $window = $this->getWindow();

    $sMethod = $this->getCallShort($el->getName());
    $method = $this->getInterface()->loadMethod($sMethod);

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

}

