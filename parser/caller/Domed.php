<?php

namespace sylma\parser\caller;
use sylma\core, sylma\storage\fs, sylma\dom, sylma\parser\action\php;

require_once('core/module/Argumented.php');

class Domed extends core\module\Argumented {

  protected $file;
  protected $document;
  protected $aMethods = array();

  public function __construct(core\factory $controler, core\argument $doc) {

    $this->setControler($controler);
    $this->setNamespace($this->getNamespace(), 'self');

    $doc->registerToken('method', 'method', 'name');

    $this->setArguments($doc);
  }

  public function parseCall(dom\element $el, php\basic\_ObjectVar $obj) {

    $window = $obj->getControler();
    $sMethod = $el->getAttribute('name');

    if (!$sMethod) {

      $this->throwException(txt('No method defined for call in %s', $el->asToken()));
    }

    $method = $this->getMethod($sMethod);
    $aArguments = $this->loadArguments($el);

    $call = $method->reflectCall($obj->getControler(), $obj, $aArguments);

    return $this->runCall($call, $el);
  }

  protected function parseElement(dom\element $el) {

    if ($el->getNamespace() == $this->getNamespace()) {

      $mResult = $this->parseElementSelf($el);
    }
    else {

      $mResult = $this->parseElementForeign($el);
    }

    return $mResult;
  }

  protected function parseElementForeign(dom\element $el) {

    $mResult = null;

    if ($el->getNamespace() == $this->getParent()->getNamespace()) {

      $mResult = $this->getParent()->parseElementSelf($el);
    }
    else {

      $this->throwException(txt('Invalid %s, action\'s element expected', $el->asToken()));
    }

    return $mResult;
  }

  protected function parseElementSelf(dom\element $el) {

    switch ($el->getName()) {

      case 'call' : $mResult = $this->parseCall($el); break;
      case 'argument' : $mResult = $this->parseArgument($el); break;

      default :

        $this->throwException(txt('Invalid %s, call or argument expected', $el->asToken()));
    }

    return $mResult;
  }

  protected function parseArgument() {

    return null;
  }

  protected function loadArguments(dom\element $el) {

    $aResult = array();

    foreach ($el->getChildren() as $child) {

      if ($child->getNamespace() == $this->getNamespace()) break;
      $aResult[] = $this->parseElement($child);
      $child->remove();
    }

    return $aResult;
  }

  protected function runCall(php\basic\CallMethod $call, dom\element $el) {

    if ($el->hasChildren()) {

      $window = $call->getControler();

      $var = $call->getVar($mResult);
      $window->setScope($var);

      $interface = $this->getControler()->loadObject($var);

      if ($el->getChildren()->length == 1) {

        $mResult = $interface->parseCall($el);
      }
      else {

        $mResult = array();

        foreach ($el->getChildren() as $child) {

          $mResult[] = $interface->parseCall($el);
        }
      }

      $window->stopScope();
    }
    else {

      $mResult = $call;
    }

    return $mResult;
  }

  public function getMethod($sName) {

    if (!array_key_exists($sName, $this->aMethods)) {

      $this->aMethods[$sName] = $this->loadMethod($sName);
    }

    return $this->aMethods[$sName];
  }

  protected function loadMethod($sMethod) {

    $method = $this->getArgument('#method:'. $sMethod);
    $aArguments = array();

    foreach ($method as $sElement => $arg) {

      if ($sElement != 'argument' || $arg->getNamespace() != $this->getNamespace()) {

        $this->throwException(txt('Invalid %s', $arg->asToken()));
      }

      $sName = $arg->read('@name');

      $aArguments[$sName] = array(
        'format' => $arg->read('@format'),
        'required' => $arg->read('@required'),
      );
    }

    $controler = $this->getControler();
    $result = $controler->create('method', array($controler, $method->read('@name'), $method->read('@return'), $aArguments));

    return $result;
  }

  protected function parseDocument(dom\handler $doc) {

    $window = $this->getWindow();

    $call = $window->createCall($obj, $sMethod, '\sylma\storage\fs\directory', array());
  }

  protected function throwException($sMessage, $mSender = array(), $iOffset = 2) {

    $mSender[] = $this->getFile()->asToken();

    parent::throwException($sMessage, $mSender, $iOffset);
  }

}