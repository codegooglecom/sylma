<?php

namespace sylma\parser\languages\common\basic;
use sylma\core, sylma\dom, sylma\parser\languages\common;

abstract class Window extends core\module\Domed {

  // Indexed
  protected $aContent = array();

  /**
   * Stack of scopes added (ie: control structure, if, when, etc..)
   * @var array
   */
  protected $aScopes = array();

  protected $aKeys = array();

  // $this reference object
  protected $self;

  protected $aVariables = array();

  public function getSelf() {

    return $this->self;
  }

  public function createArgument($mArguments, $sNamespace = '') {

    return parent::createArgument($mArguments, $sNamespace);
  }

  public function flattenArray(array $aContent) {

    $aResult = array();

    foreach ($aContent as $mContent) {

      if (is_array($mContent)) {

        if ($mContent) {

          $aResult = array_merge($aResult, $this->flattenArray($mContent));
        }
      }
      else if (!is_null($mContent)) {

        $aResult[] = $mContent;
      }
    }

    return $aResult;
  }

  public function add($mVal) {

    return $this->getScope()->addContent($mVal);
  }

  public function addContent($mVal) {

    if (is_null($mVal)) {

      $mResult = null;
    }
    else if (is_array($mVal)) {

      $mResult = array();

      foreach ($mVal as $mChild) {

        $mResult[] = $this->addContent($mChild);
      }
    }
    else {

      $mResult = $this->addContentUnknown($mVal);
    }

    return $mResult;
  }

  public function checkContent($mVal) {

    if ($mVal instanceof common\ghost) {

      $this->throwException('Cannot add ghost to content');
    }
    else if (is_object($mVal) && !$mVal instanceof common\argumentable) {

      $this->throwException(sprintf('Cannot add %s to content', $this->show($mVal)));
    }

    return $mVal;
  }

  protected function addContentUnknown($mVal) {

    $this->aContent[] = $this->createInstruction($this->checkContent($mVal));
  }

  protected function loadReturn($mReturn) {

    if (is_string($mReturn)) {

      $result = $this->createGhost($mReturn);
    }
    else if (is_array($mReturn)) {

      $this->throwException('Cannot convert yet array to return');
    }
    else if (is_null($mReturn)) {

      $result = $mReturn;
    }
    else if ($mReturn instanceof common\ghost) {

      $result = $mReturn;
    }
    else if ($mReturn instanceof common\_instance) {

      $result = $mReturn->getInterface();

    } else {

      $this->throwException(sprintf('Cannot convert to return %s', $this->show($mReturn)));
    }

    return $result;
  }

  public function createGhost($sClass) {

    return null;
  }

  public function createAssign($to, $value, $sPrefix = '') {

    return $this->create('assign', array($this, $to, $value, $sPrefix));
  }

  public function createString($mContent) {

    if (is_string($mContent)) {

      $result = $this->create('string', array($this, $mContent));
    }
    else {

      $result = $this->argToString($mContent);
    }

    return $result;
  }

  protected function argToString($mValue) {

    return $this->create('string', array($this, $mValue));
  }

  public function createInstruction(common\argumentable $content) {

    return $this->create('instruction', array($this, $content));
  }

  public function createInstanciate(common\_instance $instance, array $aArguments = array()) {

    return $this->create('instanciate', array($this, $instance, $aArguments));
  }

  public function createLoop($looped, common\_var $var) {

    if (!$looped) {

      $this->throwException('Looped object required');
    }

    return $this->create('loop', array($this, $looped, $var));
  }

  public function setVariable(common\_var $var) {

    $sName = $var->getName();

    if (array_key_exists($sName, $this->aVariables)) {

      $this->throwException(sprintf('Variable %s ever registered', $sName));
    }

    $this->aVariables[$sName] = $var;
  }

  public function getVariable($sName) {

    if (array_key_exists($sName, $this->aVariables)) {

      $result = $this->aVariables[$sName];
    }
    else {

      $this->throwException(sprintf('No variable with name %s', $sName));
    }

    return $result;
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

  public function argToInstance($mVar) {

    if (is_object($mVar)) {

      $result = $this->objectToInstance($mVar);
    }
    else {

      $result = $this->typeToInstance(gettype($mVar), $mVar);
    }

    return $result;
  }

  protected function typeToInstance($sFormat, $mVar = null) {

    $result = null;

    switch ($sFormat) {

      case 'boolean' :

        if (is_null($mVar)) $mVar = false;
        $result = $this->create('boolean', array($this, $mVar));

      break;

      case 'integer' :
      case 'numeric' :
      case 'double' :

        if (is_null($mVar)) $mVar = 0;
        $result = $this->create('numeric', array($this, $mVar));

      break;

      case 'string' :

        if (is_null($mVar)) $mVar = '';
        $result = $this->createString($mVar);

      break;

      case 'array' :

        if (is_null($mVar)) $mVar = array();
        $result = $this->create('array', array($this, $mVar));

      break;

      case 'NULL' :
      case 'null' :

        $result = $this->create('null', array($this));

      break;

      default :

        $this->throwException(sprintf('Unkown scalar type as argument : %s', $sFormat));
    }

    return $result;
  }

  protected function objectToInstance($obj) {

    if ($obj instanceof common\argumentable) {

      $result = $obj;
    }
    else if ($obj instanceof dom\node) {

      $result = $this->nodeToInstance($obj);
    }
    else {

      $result = $this->objectUnknownToInstance($obj);
    }

    return $result;
  }

  protected function nodeToInstance(dom\node $node) {

    $this->throwException('Cannot handle dom');
  }

  protected function objectUnknownToInstance($obj) {

    $this->throwException(sprintf('Cannot transform object of @class %s to instance', get_class($obj)));
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