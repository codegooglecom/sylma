<?php

namespace sylma\parser\languages\php\basic;
use sylma\core, sylma\dom, sylma\parser\languages\php, sylma\parser\languages\common, sylma\storage\fs;

class Window extends common\basic\Window implements php\window {

  protected $aManagers = array();

  protected static $sArgumentClass = '\sylma\parser\languages\php\Argument';
  protected static $sArgumentFile = 'parser/languages/php/Argument.php';

  // Keyed by file path. ex : #sylma/action/index.xsl
  //protected $aDependencies = array();

  protected $aInterfaces = array();

  // static reference to class
  protected $sylma;
  protected $return;

  public function __construct($controler, core\argument $args, $sClass) {

    $this->setControler($controler);
    $this->setArguments($args);
    $this->setNamespace(self::NS);

    $self = $this->loadInstance($sClass);

    $this->self = $this->create('object-var', array($this, $self, 'this'));
    $this->setScope($this);

    $node = $this->loadInstance('\sylma\dom\node');
    $this->setInterface($node->getInterface());

    $this->sylma =  $this->createClass('\Sylma');
  }

  protected function addContentUnknown($mVal) {

    $mVal = $this->transformContent($mVal);
    //$this->loadContent($mVal);

    $this->aContent[] = $mVal;
  }

  public function transformContent($mVal) {

    if (!$mVal instanceof dom\node && !$mVal instanceof common\structure && !$mVal instanceof common\instruction) {

      $mResult = $this->create('line', array($this, $mVal));
    }
    else {

      $mResult = $mVal;
    }

    return $mResult;
  }

  public function addControler($sName, $from = null) {

    if (!$from) $from = $this->getSylma();

    if (!array_key_exists($sName, $this->aManagers)) {

      $controler = $this->getControler($sName);
      $return = $this->tokenToInstance(get_class($controler));

      $call = $this->createCall($from, 'getManager', $return, array($sName));

      if (!$this->isRendering()) {

        $this->aManagers[$sName] = $call->getVar();
      }
      else {

        $this->aManagers[$sName] = $call;
      }
    }

    return $this->aManagers[$sName];
  }

  public function getSylma() {

    return $this->sylma;
  }

  public function createCall($obj, $sMethod, $mReturn = null, array $aArguments = array()) {

    if (is_null($obj)) {

      $this->throwException('NULL sent instead of callable object');
    }

    if (is_null($mReturn)) $mReturn = $this->argToInstance(null);

    $result = $this->create('call-method', array($this, $obj, $sMethod, $this->loadReturn($mReturn), $aArguments));

    return $result;
  }

  public function callFunction($sName, $mReturn = null, array $aArguments = array()) {

    return $this->create('function', array($this, $sName, $this->loadReturn($mReturn), $aArguments));
  }

  /**
   * @return
   */
  public function createClosure(array $aArguments = array()) {

    return $this->create('closure', array($this, $aArguments));
  }

  public function createClass($sName) {

    return $this->create('class', array($this, $this->getObjectInterface($this->loadReturn($sName))));
  }

  protected function getObjectInterface(common\_object $obj) {

    return $obj->getInterface();
  }

  public function callClosure($closure, common\_instance $return, array $aArguments = array()) {

    return $this->create('call', array($this, $closure, $return, $aArguments));
  }

  public function isStatic($mVal) {

    switch (gettype($mVal)) {

      case 'array' :

        foreach ($mVal as $mSub) {

          if (!$this->isStatic($mSub)) {

            return false;
          }
        }

      case 'double' :
      case 'integer' :
      case 'string' :
      case 'boolean' :

        return true;

      case 'object' :

        if (
                $mVal instanceof common\operator
        ) {

          return true;
        }
    }

    return false;
  }

  /**
   * Temp for common\concat use
   * @param type $mValue
   * @return type
   */
  public function convertToString($mValue) {

    if (is_string($mValue)) {

      $result = $this->createString($mValue);
    }
    else {

      $result = $mValue;
    }

    return $result;
  }

  protected function argToString($mValue) {

    if ($mValue instanceof core\stringable || $mValue instanceof Cast) {

      $result = $this->create('string', array($this, $mValue));
    }
    else {

      $result = $this->create('concat', array($this, $mValue));
    }

    return $result;
  }

  public function createCondition($test = null, $content = null) {

    return $this->create('condition', array($this, $test, $content));
  }

  public function createSwitch($test) {

    return $this->create('switch', array($this, $test));
  }

  public function createCase($test, $content = null) {

    return $this->create('case', array($this, $test, $content));
  }

  protected function lookupInstance($val) {

    if ($val instanceof common\usable) {

      $result = $this->lookupInstanceObject($val);
    }
    else {

      $result = $this->tokenToInstance($val);
    }

    return $result;
  }

  /**
   *
   * @param null $mValue
   * @param type $sName
   * @param type $bContent
   *    If TRUE, $mValue is assigned as content
   *    if FALSE it's used only for getting return type
   * @param type $bDebug
   * @return type
   */
  public function createVar($mValue, $sName = '', $bContent = true, $bDebug = true) {

    $return = $this->lookupInstance($mValue);
    if (!$bContent) $mValue = null;

    if ($return instanceof common\_object) $sAlias = 'object-var';
    else $sAlias = 'simple-var';

    if (!$sName) {

      $sName = $this->getVarName();
      $result = $this->create($sAlias, array($this, $return, $sName, $mValue));
    }
    else {

      $result = $this->create($sAlias, array($this, $return, $sName, $mValue));
      $this->setVariable($result, $bDebug);
    }

    return $result;
  }

  protected function loadReturn($mReturn) {

    if (is_string($mReturn)) {

      $result = $this->tokenToInstance($mReturn);
    }
    else {

      $result = $mReturn;
    }

    return $result;
  }

  protected function getReturn() {

    return $this->return;
  }

  public function setReturn($return) {

    $this->return = $return;
  }

  public function addVar(common\argumentable $val, $sName = '') {

    $result = $val;

    if ($val instanceof common\_var) {

      $result->insert();
    }
    else if ($val instanceof common\_call) {

      $result = $val->getVar(true, $sName);
    }
    else {

      $result = $this->createVar($val, $sName);
      $result->insert();
    }

    return $result;
  }

  public function createVariable($sName = '', $mReturn = null, $bDebug = true) {

    if (!$sName) $sName = $this->getVarName();

    return $this->createVar($mReturn, $sName, false, $bDebug);
  }

  public function createNot($mContent) {

    $op = $this->createOperator('!');

    if ($mContent) {

      // return array while expecting result is only treated in condition not body result

      $result = array(
        $op,
        $mContent,
      );
    }
    else {

      $result = $op;
    }

    return $result;
  }

  public function getVarName() {

    return 'var' . $this->getKey('var');
  }

  public function setInterface(php\basic\_Interface $interface) {

    $this->aInterfaces[$interface->getName()] = $interface;
  }

  public function getInterface($sName, fs\file $file = null) {

    if (!array_key_exists($sName, $this->aInterfaces)) {

      $sNamespace = $this->getManager()->getNamespace();
      $this->aInterfaces[$sName] = $this->create('interface', array($this->getManager(), $sName, $sNamespace, $file));
    }

    return $this->aInterfaces[$sName];
  }

  public function tokenToInstance($sFormat) {

    $result = null;

    if (substr($sFormat, 0, 4) == 'php-') {

      $result = $this->typeToInstance(substr($sFormat, 4));
    }
    else {

      $result = $this->loadInstance($sFormat);
    }

    return $result;
  }

  public function loadInstance($sName, fs\file $file = null) {

    $interface = $this->getInterface($sName, $file);
    $result = $this->create('object', array($this, $interface));

    return $result;
  }

  protected function nodeToInstance(dom\node $node) {

    if ($node instanceof dom\handler) {

      $sNamespace = $node->getRoot()->getNamespace();
    }
    else if ($node instanceof dom\element) {

      $sNamespace = $node->getNamespace();
    }
    else {

      $this->throwException(sprintf('Cannot convert %s to instance', $node->asToken()));
    }

    if ($sNamespace == $this->getNamespace()) {

      $result = $node;
    }
    else {

      $result = $this->createTemplate($node);
    }

    return $result;
  }

  public function checkVariable($sName, $sToken) {

    if (!$var = $this->getVariable($sName, false)) {

      $var = $this->createVariable($sName, $sToken);
    }

    $this->buildVariable($var);

    return $var;
  }

  protected function buildVariable(common\_var $var) {

    $isset = $this->callFunction('isset', $this->tokenToInstance('php-boolean'), array($var));
    $new = $this->createInstanciate($var->getInstance());

    $this->add($this->createCondition($this->createNot($isset), $this->createAssign($var, $new)));
  }

  public function getAbsoluteClass($sPath, $sDirectory = '/') {

    if ($sPath{0} == '\\') {

      $sResult = $sPath;
    }
    else {

      $aPaths = explode('\\', $sPath);
      $aDirectories = explode('/', $sDirectory);

      foreach ($aPaths as $sPath) {

        if ($sPath == '..') {

          array_shift($aPaths);
          array_pop($aDirectories);
        }
        else {

          break;
        }
      }

      \Sylma::load('/core/functions/Path.php');
      $sResult = implode(array_merge($aDirectories, $aPaths), '\\');
    }

    return $sResult;
  }

  public function createArgument($mArguments, $sNamespace = '') {

    $result = parent::createArgument($mArguments, $sNamespace);
    $result->setWindow($this);

    return $result;
  }

  public function asArgument() {

    if ($this->getReturn()) {

      $return = $this->createArgument(array('line' => array('return' => $this->getReturn())));
      $this->aContent[] = $return;
    }

    return parent::asArgument();
  }


  /*public function validateFormat(common\_var $var, $sFormat) {

    $condition = $this->create('condition', array($this, $this->create('not', array($test))));
    $text = $this->create('function', array($this, 't', array('Bad argument format')));
    $condition->addContent($this->createCall($this->getSelf(), 'throwException', null, array($text)));
  }*/
}