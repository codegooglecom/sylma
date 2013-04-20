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

  /**
   * Scope variables
   */
  protected $aVariables = array();

  /**
   * Set to TRUE when rendering
   */
  protected $bRender = false;

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

    if ($this->isRendering()) {

      $this->launchException('Too late to add content, window is rendering', get_defined_vars());
    }

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

  abstract protected function addContentUnknown($mVal);

  public function checkContent($mVal) {

    if ($mVal instanceof common\ghost) {

      $this->throwException('Cannot add ghost to content');
    }
    else if (is_object($mVal) && !$mVal instanceof common\usable) {

      $this->throwException(sprintf('Cannot add %s to content', $this->show($mVal)));
    }

    return $mVal;
  }
/*
  protected function addContentUnknown($mVal) {

    $this->aContent[] = $this->createInstruction($this->checkContent($mVal));
  }
*/
  public function loadContent($content) {

    if (is_array($content)) {

      foreach ($content as $item) {

        $this->loadContent($item);
      }
    }
    else if ($content instanceof common\addable) {

      $content->onAdd();
    }
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

  public function createCast($content, $sType = 'string') {

    return $this->create('cast', array($this, $content, $sType));
  }

  protected function lookupInstanceObject(common\usable $val) {

    if ($val instanceof common\_call) {

      $result = $val->getReturn();
    }
    else if ($val instanceof common\_closure) {

      $result = $this->lookupInstanceObject($val->getReturn());
    }
    else if ($val instanceof common\_var) {

      $result = $val->getInstance();
    }
    else if ($val instanceof dom\node) {

      $result = $this->getInterface('\sylma\dom\node');
    }
    else {

      $result = $val;
    }

    return $result;
  }

  public function parseArrayables(array $aContent) {

    //$aContent =
    $aResult = array();

    foreach ($aContent as $mVal) {

      if (is_array($mVal)) {

        $aResult[] = $this->parseArrayables($mVal);
      }
      else if ($mVal instanceof common\arrayable) {

        $aResult[] = $this->parseArrayable($mVal);
      }
      else {

        $aResult[] = $mVal;
      }
    }

    return $this->flattenArray($aResult);
  }

  protected function parseArrayable(common\arrayable $val) {

    $aResult = $val->asArray();

    return $this->parseArrayables($aResult);
  }

  protected function argToString($mValue) {

    return $this->create('string', array($this, $mValue));
  }

  protected function arrayToString(array $mContent, common\_var $target = null) {

    $aContent = $this->parseArrayables($mContent);
    $aResult = array();

    foreach ($aContent as $mVal) {

      if ($mVal instanceof common\structure) {

        $this->addToResult($aResult, $target);
        $this->add($mVal);

        $aResult = array();
      }
      else {

        $aResult[] = $this->prepareToString($mVal);
      }
    }

    if ($aResult) $result = $this->createString($aResult);
    else $result = null;

    return $result;
  }

  protected function prepareToString($val) {

    if ($val instanceof common\usable) {

      $instance = $this->lookupInstanceObject($val);

      if ($instance instanceof common\_object && $instance->getInterface()->isInstance('\sylma\core\stringable')) {

        $result = $this->createCast($val);
      }
      else {

        $result = $val;
      }
    }
    else {

      $result = $val;
    }

    return $result;
  }

  public function toString($mContent, common\_var $target = null) {

    if (is_array($mContent)) {

      $result = $this->arrayToString($mContent, $target);
    }
    else if (is_object($mContent)) {

      $result = $this->objectToString($mContent);
      //$result = $mContent;
    }
    else {

      $result = $this->arrayToString(array($mContent));
    }

    return $result;
  }

  protected function objectToString($val) {

    if ($val instanceof common\arrayable) {

      $result = $this->arrayToString($this->parseArrayable($val));
    }
    else if ($val instanceof core\argument) {

      $result = $val;
    }
    else {

      $this->throwException(sprintf('Cannot add %s to result', $this->show($val)));
    }

    return $result;
  }

  public function addToResult($mContent, common\_var $target, $bAdd = true) {

    $content = $this->toString($mContent, $target);

    if ($content) {

      $result = $this->createAssign($target, $content, '.');
      if ($bAdd) $this->add($result);
    }
    else {

      $result = null;
    }

    return $result;
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

  public function createTest($val1, $val2, $op = '==') {

    return $this->create('test', array($this, $val1, $val2, $op));
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

    if ($obj instanceof common\usable) {

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

  protected function isRendering($bValue = null) {

    if (!is_null($bValue)) $this->bRender = $bValue;

    return $this->bRender;
  }

  public function asArgument() {

    $result = $this->createArgument(array('window' => array()));
    $result->get('window')->mergeArray($this->aContent);

    return $result;
  }

  public function asDOM() {

    $this->isRendering(true);

    $arg = $this->asArgument();
    $result = $arg->asDOM();

    $this->isRendering(false);

    return $result;
  }
}