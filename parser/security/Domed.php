<?php

namespace sylma\parser\security;
use sylma\core, sylma\parser, sylma\dom, sylma\parser\languages\php;

\Sylma::load('/parser/reflector/attributed.php');
\Sylma::load('Reflector.php', __DIR__);

class Domed extends Reflector implements parser\reflector\attributed {

  const NS = 'http://www.sylma.org/parser/security';

  protected $element;

  public function parseAttributes(dom\node $el, dom\element $resultElement, $result) {

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

  public function onClose(dom\element $el, dom\element $newElement) {


  }

  protected function parseElement(dom\node $el) {

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