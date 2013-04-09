<?php

namespace sylma\parser\security;
use sylma\core, sylma\parser\reflector, sylma\dom, sylma\parser\languages\php;

class Domed extends Main implements reflector\attributed {

  const NS = 'http://2013.sylma.org/parser/security';

  protected $element;

  public function init() {


  }

  public function parseAttributes(dom\element $el, $resultElement, $result) {

    if (!is_object($result)) {

      $this->throwException(sprintf('Bad type for result : %s', $this->show($result)));
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

  public function onClose(dom\element $el, $newElement) {


  }

  protected function parseElement(dom\element $el) {

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