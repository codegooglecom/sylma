<?php

namespace sylma\parser\security;
use sylma\core, sylma\parser, sylma\dom, sylma\parser\action\php;

require_once('parser/attributed.php');
require_once('Reflector.php');

class Domed extends Reflector implements parser\attributed {

  const NS = 'http://www.sylma.org/parser/security';

  protected $parent;
  protected $element;

  public function parseAttributes(dom\node $el, dom\element $resultElement, $result) {

    if (!is_object($result)) {

      $formater = $this->getControler('formater');
      $this->throwException(txt('Bad type for result : %s', $formater->asToken($result)));
    }

    if ($result instanceof php\basic\Condition) {

      $window = $this->getParent()->getWindow();

      $resultTest = $result->getTest();
      $test = $this->reflectTest($this->parseElement($el));

      $result->setTest($window->create('test', array($window, $resultTest, $test, '&&')));
    }
    else {

      $aRights = $this->parseElement($el);
      $result = $this->reflectRights($result, $aRights);
    }

    return $result;
  }

  public function parseElement(dom\node $el) {

    $sOwner = $el->readAttribute('owner', $this->getNamespace());
    $sGroup = $el->readAttribute('group', $this->getNamespace());
    $sMode = $el->readAttribute('mode', $this->getNamespace());

    return array(
      'user' => $sOwner,
      'group' => $sGroup,
      'mode' => intval($sMode)
    );
  }
}