<?php

namespace sylma\parser\action\compiler;
use \sylma\core, \sylma\dom, \sylma\parser\action\php;

require_once('Action.php');

abstract class Runner extends Action {

  /**
   *
   * @param php\_var $var
   * @param dom\collection $children
   * @return array|\sylma\parser\action\php\_var
   */
  public function runVar(php\_var $var, dom\collection $children) {

    $aResult = array();

    if ($children->current()) {

      $var->insert();

      $window = $this->getWindow();
      $window->setScope($var);

      $caller = $this->getControler('caller');
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

      $window->stopScope();
    }

    return $aResult;
  }

  /**
   *
   * @param php\basic\CallMethod $call
   * @param dom\collection $children
   * @return array
   */
  public function runConditions(php\_var $call, dom\collection $children) {

    $aResult = array();

    while ($child = $children->current()) {

      if ($child->getNamespace() == $this->getNamespace()) {

        // from here, condition can be builded

        $sName = $child->getName();
        $window = $this->getWindow();

        if ($child->getChildren()->length != 1) {

          $this->throwException(txt('Invalid children, one child expected in %s', $child->asToken()));
        }

        $content = $this->parse($child->getFirst());
        $var = $window->createVar($content);

        $var->insert($window->stringToInstance('php-null'));
        //$window->add($window->create('assign', array($window, $var, )));
        $assign = $window->create('assign', array($window, $var, $content));

        $call->insert();

        if ($sName == 'if') {

          $condition = $window->create('condition', array($window, $call, $assign));
        }
        else if ($sName == 'if-not') {

          $not = $window->createNot($call);
          $condition = $window->create('condition', array($window, $not, $assign));
        }
        else {

          $this->throwException(txt('Condition expected, invalid %s', $child->asToken()));
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
}
