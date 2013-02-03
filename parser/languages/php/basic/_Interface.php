<?php

namespace sylma\parser\languages\php\basic;
use sylma\core, sylma\parser\languages\common, sylma\parser\languages\php, sylma\dom, sylma\parser;

class _Interface extends core\module\Argumented {

  protected $sName = '';
  protected $reflection;
  protected $file = null;

  protected $aMethods = array();

  public function __construct(parser\reflector\documented $reflector, $sInterface, fs\file $file = null) {

    $this->setManager($reflector);
    $this->setName($sInterface);

    $this->setNamespace($this->getManager()->getNamespace());

    if ($file) $this->setFile($file);
  }

  protected function setFile(fs\file $file) {

    $this->file = $file;
  }

  protected function getFile() {

    return $this->file;
  }

  public function getName() {

    return $this->sName;
  }

  protected function setName($sInterface) {

    if (!preg_match('/^[\w_\\\]*$/', $sInterface)) {

      $this->throwException(sprintf('Invalid class name : %s', $sInterface));
    }
    else if (!$sInterface) {

      $this->throwException('Empty name not allowed');
    }

    $this->sName = $sInterface;
  }

  /**
   * Made public for child methods
   * @return \ReflectionClass
   */
  public function getReflection() {

    return $this->reflection;
  }

  public function getExtension() {

    $result = null;
    $this->loadReflection();

    if ($sExtension = get_parent_class($this->getName())) {

      $result = new static($this->getManager(), $sExtension);
    }

    return $result;
  }

  /**
   *
   * @return common\_window
   */
  public function getWindow() {

    return $this->getManager()->getWindow();
  }

  protected function loadReflection() {

    if (!$this->reflection) {

      $factory = $this->getFactory();
      $factory::includeClass($this->getName(), $this->getFile());

      $this->reflection = new \ReflectionClass($this->getName());
    }
  }

  public function isInstance($sInterface) {

    if ($this->getName() == $sInterface) return true;

    $this->loadReflection();

    if (!$reflection = $this->getReflection()) {

      $this->throwException(sprintf('No reflector implemented, cannot find @class %s', $this->getName()));
    }

    return $reflection->implementsInterface($sInterface);
  }

  public function loadCall(_ObjectVar $var, Method $method, dom\collection $args) {

    $aArguments = $this->parseArguments($args);

    $call = $method->reflectCall($var->getControler(), $var, $aArguments);

    return $call;
  }

  protected function parseArgument(dom\element $el, $iKey) {

    if (!$mKey = $el->readAttribute('name', $this->getNamespace(), false)) {

      $mKey = $iKey;
    }

    return array(
      'name' => $mKey,
      'value' => $this->getManager()->parse($el),
    );
  }

  protected function parseArguments(dom\collection $children) {

    $aResult = array();
    $iKey = 0;

    while ($child = $children->current()) {

      switch ($child->getType()) {

        case dom\node::TEXT :

          $aResult[] = $this->getManager()->parse($child);

        break;

        case dom\node::ELEMENT :

          if ($child->isElement('call', $this->getNamespace())) {

            break 2;
          }
          else if ($child->getNamespace() == $this->getNamespace()) {

            if (in_array($child->getName(), array('if', 'if-not'))) {

              break 2;
            }
          }

          $aArgument = $this->parseArgument($child, $iKey);
          $aResult[$aArgument['name']] = $aArgument['value'];
          $child->remove();

        break;

        default :

          $this->throwException(sprintf('Cannot use %s, valid argument expected', $child->asToken()));
      }

      $children->next();
      $iKey++;
    }

    return $aResult;
  }

  public function getMethod($sName) {

    if (!array_key_exists($sName, $this->aMethods)) {

      $this->aMethods[$sName] = $this->loadMethod($sName);
    }

    return $this->aMethods[$sName];
  }

  public function loadMethod($sName) {

    $result = $this->getWindow()->create('method', array($this, $sName));
    $this->aMethods[$sName] = $result;

    return $result;
  }

  public function addInstance(common\_window $window, dom\collection $children) {

    $aArguments = $this->parseArguments($children);

    if ($file = $this->getFile()) {

      $require = $window->callFunction('require_once', $window->argToInstance('php-bool'), array($file->getRealPath()));
      $window->add($require);
    }

    $instance = $window->loadInstance($this->getName(), $file);

    return $window->createInstanciate($instance, $aArguments);
  }

  protected function throwException($sMessage, $mSender = array(), $iOffset = 2) {

    $mSender['@class'] = $this->getName() . 'in' . $this->getFile() ? $this->getFile()->asToken() : '[no-file]';
    parent::throwException($sMessage, $mSender, $iOffset);
  }
}