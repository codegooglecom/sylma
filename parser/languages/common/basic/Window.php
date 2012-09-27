<?php

namespace sylma\parser\languages\common\basic;
use sylma\parser, sylma\core, sylma\dom, sylma\parser\languages\php, sylma\parser\languages\common;

abstract class Window extends core\module\Domed {

  // Indexed
  protected $aContent = array();

  /**
   * Stack of scopes added (ie: control structure, if, when, etc..)
   * @var array
   */
  protected $aScopes = array();

  protected $aObjects = array();

  protected $aKeys = array();

  public function createArgument($mArguments, $sNamespace = '') {

    return parent::createArgument($mArguments, $sNamespace);
  }

  public function add($mVal) {

    $this->getScope()->addContent($mVal);
  }

  public function addContent($mVal) {

    if (is_array($mVal)) {

      foreach ($mVal as $mChild) {

        $this->addContent($mChild);
      }
    }
    else {

      if ($mVal instanceof common\_var) {

        $mVal->insert();
      }
      else {

        $this->addContentUnknown($mVal);
      }
    }
  }

  protected function addContentUnknown($mVal) {

    $this->aContent[] = $mVal;
  }

  protected function loadReturn($mReturn) {

    if (is_string($mReturn)) {

      $result = $this->loadInstance($mReturn);
    }
    else {

      $result = $mReturn;
    }

    return $result;
  }

  public function createProperty($obj, $sName, $mReturn) {

    $result = $this->create('property', array($this, $obj, $sName, $this->loadReturn($mReturn)));

    return $result;
  }

  public function createCall($obj, $sName, $mReturn, array $aArguments = array()) {

    $result = $this->create('call', array($this, $obj, $sName, $this->loadReturn($mReturn), $aArguments));

    return $result;
  }

  public function createFunction(array $aArguments = array()) {

    return $this->create('function', array($this, $aArguments));
  }

  public function createVar(common\linable $val) {

    $return = $val;

    return $this->create('variable', array($this, $return, $this->getVarName(), $val));
  }

  public function getVarName() {

    return 'var' . $this->getKey('var');
  }

  public function getKey($sPrefix) {

    if (array_key_exists($sPrefix, $this->aKeys)) {

      $this->aKeys[$sPrefix]++;
    }
    else {

      $this->aKeys[$sPrefix] = 0;
    }

    return $this->aKeys[$sPrefix];
  }

  public function createInstanciate(common\_instance $instance, array $aArguments = array()) {

    return $this->create('instanciate', array($this, $instance, $aArguments));
  }

  public function getScope() {

    if (!$this->aScopes) {

      $this->throwException('Cannot get scope, no scope defined');
    }

    return end($this->aScopes);
  }

  public function setScope(common\scope $scope) {

    $this->aScopes[] = $scope;
  }

  public function stopScope() {

    if (!$this->aScopes) {

      $this->throwException(t('Cannot stop scope, no scope defined'));
    }

    return array_pop($this->aScopes);
  }

  public function getObject() {

    if (!$this->aObjects) {

      $this->throwException(t('Cannot get object, no object defined'));
    }

    return $this->aObjects[count($this->aObjects) - 1];
  }

  public function setObject(common\_object $obj) {

    $this->aObjects[] = $obj;
  }

  public function stopObject() {

    if (!$this->aObjects) {

      $this->throwException(t('Cannot stop object scope, no object defined'));
    }

    return array_pop($this->aObjects);
  }

  public function loadInstance($sClass) {

    $result = $this->create('object', array($this, $sClass));

    return $result;
  }

  public function argToInstance($mVar) {

    $arg = null;

    if (is_object($mVar)) {

      if ($mVar instanceof dom\node) {

        $arg = $this->createTemplate($mVar);
      }
      else if ($mVar instanceof common\_instance ||
          $mVar instanceof php\basic\Called ||
          $mVar instanceof php\basic\_Closure ||
          $mVar instanceof common\_var) {

        $arg = $mVar;
      }
      else {

        $arg = $this->loadInstance(get_class($mVar));
      }
    }
    else if (is_null($mVar)) {

      $arg = $this->create('null', array($this));
    }
    else {

      $this->throwException(sprintf('Cannot transform value %s', $this->show($mVar)));
    }

    return $arg;
  }

  public function throwException($sMessage, $mSender = array(), $iOffset = 2) {

    return parent::throwException($sMessage, $mSender, $iOffset);
  }

  public function asArgument() {

    $result = $this->createArgument(array('window' => array()));
    $result->get('window')->mergeArray($this->aContent);

    return $result;
  }

}