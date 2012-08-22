<?php

namespace sylma\parser\action\compiler;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\parser\languages\php, sylma\parser;

require_once('Action.php');

abstract class Runner extends Action {

  /**
   *
   * @param common\_var $var
   * @param dom\collection $children
   * @return array|\sylma\parser\languages\common\_var
   */
  public function runVar(common\_var $var, dom\collection $children) {

    $aResult = array();

    if ($children->current()) {

      $var->insert();

      $window = $this->getWindow();
      $window->setObject($var);

      $caller = $this->getControler(self::CALLER_ALIAS);
      $interface = $caller->loadObject($var);

      while ($child = $children->current()) {

        $children->next();
        $call = $interface->parseCall($child, $var);

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
  public function runConditions(common\_var $call, dom\collection $children) {

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

        $var->insert($window->stringToInstance('php-null'));
        //$window->add($window->create('assign', array($window, $var, )));
        $assign = $window->create('assign', array($window, $var, $content));

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

  public function runObject(dom\element $el, php\basic\_ObjectVar $var, parser\caller\Method $method = null) {

    $children = $el->getChildren();

    if ($method) {

      $interface = $this->getControler(self::CALLER_ALIAS)->loadObject($var);
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
}
