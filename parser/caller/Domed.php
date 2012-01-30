<?php

namespace sylma\parser\caller;
use sylma\core, sylma\storage\fs, sylma\dom, sylma\parser\action\php;

require_once('core/module/Argumented.php');

class Domed extends core\module\Argumented {

  protected $file;
  protected $document;
  protected $aMethods = array();

  protected $sName;

  public function __construct(Controler $controler, fs\file $file) {

    $this->setControler($controler);
    $this->setFile($file);

    $this->setArguments($file->getArgument());
    $this->getArguments()->registerToken('method', 'method', 'path');
    $this->getArguments()->registerToken('element', 'method', 'element');

    if ($sElement = $this->readArgument('element', false)) {

      $this->setNamespace($sElement, 'element', false);
    }

    $this->setNamespace($this->readArgument('namespace'), 'php', false);
    $this->setName($this->readArgument('name'));
  }

  public function getFile() {

    return $this->file;
  }

  public function setFile(fs\file $file) {

    $this->file = $file;
  }

  public function getName() {

    return $this->sName;
  }

  public function setName($sNamespace) {

    $this->sName = $sNamespace;
  }

  public function parseCall(dom\element $el, php\basic\_ObjectVar $obj) {

    $sMethod = $el->getAttribute('name');

    if (!$sMethod) {

      $this->throwException(txt('No method defined for call in %s', $el->asToken()));
    }

    return $this->loadCall($obj, $this->getMethod($sMethod), $el->getChildren());
  }

  public function loadCall(php\basic\_ObjectVar $obj, Method $method, dom\collection $args) {

    $aArguments = $this->parseArguments($args);

    $call = $method->reflectCall($obj->getControler(), $obj, $aArguments);

    return $this->runCall($call, $args);
  }

  protected function parseNode(dom\node $node) {

    return $this->getControler()->getParent()->parse($node);
  }

  protected function parseArgument(dom\element $el, $iKey) {

    if (!$mKey = $el->readAttribute('argument', $this->getNamespace(), false)) {

      $mKey = $iKey;
    }

    if ($el->getNamespace() == $this->getNamespace()) {

      if ($el->getName() != 'argument') {

        $this->throwException(txt('Invalid %s, argument expected', $el->asToken()));
      }

      if ($el->countChildren() > 1) {

        $this->throwException(t('There shouldn\'t have more than one child in %s', $el->asToken()));
      }

      $mResult = $this->parseNode($el->getFirst());
    }
    else {

      $mResult = $this->parseNode($el);
    }

    return $this->createArgument(array(
      'name' => $mKey,
      'value' => $mResult,
    ));
  }

  protected function parseArguments(dom\collection $children) {

    $aResult = array();
    $iKey = 0;

    foreach ($children as $child) {

      switch ($child->getType()) {

        case dom\node::TEXT :

          $aResult[] = $this->parseNode($child);

        break;

        case dom\node::ELEMENT :

          if ($child->getNamespace() == $this->getNamespace() && $child->getName() == 'call') break;

          $arg = $this->parseArgument($child, $iKey);

          $aResult[$arg->read('name')] = $arg->get('value', false);

        break;

        default :

          $this->throwException(txt('Cannot use %s, valid argument expected', $child->asToken()));
      }

      $iKey++;
    }

    return $aResult;
  }

  public function runCall(php\basic\CallMethod $call, dom\collection $children) {

    if ($children->current()) {

      $window = $call->getControler();

      $var = $call->getVar();
      $window->setScope($var);

      $interface = $this->getControler()->loadObject($var);

      $aResult = array();

      while ($child = $children->current()) {

        $children->next();
        $aResult[] = $interface->parseCall($child, $var);
      }

      if (count($aResult) == 1) $mResult = $aResult[0];
      else $mResult = $aResult;

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

  public function loadMethod($sMethod, $sToken = 'method') {

    $controler = $this->getControler();

    $arg = $this->getArgument('#' . $sToken . ':'. $sMethod);
    $result = $controler->create('method', array($this, $arg));

    return $result;
  }

  /**
   * Namespace with prefix php is used here as PHP namespaces with anti-slash instead of slash
   * @param string|null $sPrefix
   * @return string
   */
  public function getNamespace($sPrefix = null) {

    return parent::getNamespace($sPrefix);
  }

  protected function throwException($sMessage, $mSender = array(), $iOffset = 2) {

    $mSender[] = $this->getFile()->asToken();

    parent::throwException($sMessage, $mSender, $iOffset);
  }

}