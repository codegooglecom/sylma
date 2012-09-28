<?php

namespace sylma\parser\languages\php\basic;
use sylma\parser, sylma\core, sylma\dom, sylma\parser\languages\php, sylma\parser\languages\common;

class Window extends common\basic\Window implements php\window, core\controled {

  protected static $sArgumentClass = '\sylma\parser\Argument';
  protected static $sArgumentFile = 'parser/Argument.php';

  // Keyed by alias. ex : storage/fs
  protected $aControlers = array();

  // Keyed by namespace. ex : http://www.sylma.org/parser/action
  protected $aParsers = array();

  // Keyed by file path. ex : #sylma/action/index.xsl
  protected $aDependencies = array();

  protected $aInterfaces = array();

  // $this reference object
  protected $self;

  // static reference to class
  protected $sylma;

  public function __construct($controler, core\argument $args, $sClass) {

    $this->setControler($controler);
    $this->setArguments($args);
    $this->setNamespace(self::NS);

    $self = $this->loadInstance($sClass);

    $this->self = $this->create('object-var', array($this, $self, 'this'));
    $this->setScope($this);

    $node = $this->loadInstance('\sylma\dom\node', '/sylma/dom/node.php');
    $this->setInterface($node->getInterface());

    //$this->sylma = $this->create('class-static', array('\Sylma'));
  }

  protected function addContentUnknown($mVal) {

    if (!$mVal instanceof dom\node && !$mVal instanceof php\basic\Condition) {

      $mVal = $this->create('line', array($this, $mVal));
    }

    return parent::addContentUnknown($mVal);
  }

  public function addControler($sName) {

    if (!array_key_exists($sName, $this->aControlers)) {

      $controler = $this->getControler($sName);
      $return = $this->stringToInstance(get_class($controler));

      $call = $this->createCall($this->getSelf(), 'getControler', $return, array($sName));
      $this->aControlers[$sName] = $call->getVar();
    }

    return $this->aControlers[$sName];
  }

  public function getSylma() {

    return $this->sylma;
  }

  public function createCall($obj, $sMethod, $mReturn, array $aArguments = array()) {

    if (is_string($mReturn)) {

      $return = $this->stringToInstance($mReturn);
    }
    else {

      $return = $mReturn;
    }

    $result = $this->create('call', array($this, $obj, $sMethod, $return, $aArguments));

    return $result;
  }

  public function createString($mContent) {

    if (is_string($mContent)) {

      $result = $this->create('string', array($this, $mContent));
    }
    else {

      $result = $this->create('concat', array($this, $mContent));
    }

    return $result;
  }

  public function callFunction($sName, common\_instance $return = null, array $aArguments = array()) {

    return $this->create('function', array($this, $sName, $return, $aArguments));
  }

  public function createInstanciate(common\_instance $instance, array $aArguments = array()) {

    return $this->create('instanciate', array($this, $instance, $aArguments));
  }

  public function createCondition($test, $content = null) {

    return $this->create('condition', array($this, $test, $content));
  }

  public function addVar(common\linable $val) {

    $result = $val;

    if ($val instanceof common\_var) {

      $result->insert();
    }
    else if ($val instanceof php\basic\Called) {

      $result = $val->getVar();
    }
    else {

      $result = $this->createVar($val);
      $result->insert();
    }

    return $result;
  }

  public function createVar(common\linable $val) {

    if ($val instanceof php\basic\Called) {

      $return = $val->getReturn();
    }
    else if ($val instanceof common\_var) {

      $return = $val->getInstance();
    }
    else if ($val instanceof dom\node) {

      $return = $this->getInterface('\sylma\dom\node');
    }
    else {

      $return = $val;
    }

    if ($return instanceof common\_object) $sAlias = 'object-var';
    else $sAlias = 'simple-var';

    return $this->create($sAlias, array($this, $return, $this->getVarName(), $val));
  }

  public function createNot($mContent) {

    return $this->createArgument(array(
      'not' => array($mContent),
    ));
  }

  public function setInterface(php\basic\_Interface $interface) {

    $this->aInterfaces[$interface->getName()] = $interface;
  }

  public function getInterface($sName, $sFile = '') {

    if (!array_key_exists($sName, $this->aInterfaces)) {

      $this->aInterfaces[$sName] = $this->create('interface', array($this, $sName, $sFile));
    }

    return $this->aInterfaces[$sName];
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

  public function stringToInstance($sFormat) {

    $result = null;

    if (substr($sFormat, 0, 4) == 'php-') {

      $result = $this->stringToScalar(substr($sFormat, 4));
    }
    else {

      $result = $this->loadInstance($sFormat);
    }

    return $result;
  }

  protected function stringToScalar($sFormat, $mVar = null) {

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

      case 'null' :

        $result = $this->create('null', array($this));

      break;

      default :

        $this->throwException(sprintf('Unkown scalar type as argument : %s', $sFormat));
    }

    return $result;
  }

  public function loadInstance($sName, $sFile = '') {

    $interface = $this->getInterface($sName, $sFile);
    $result = $this->create('object', array($this, $interface));

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
          $mVar instanceof php\basic\Insert ||
          $mVar instanceof common\_var) {

        $arg = $mVar;
      }
      else {

        $arg = $this->loadInstance(get_class($mVar));
      }
    }
    else if (is_null($mVar) || is_resource($mVar)) {

      $arg = $this->create('null', array($this));
    }
    else {

      $arg = $this->stringToScalar(gettype($mVar), $mVar);
    }

    return $arg;
  }

  /*public function validateFormat(common\_var $var, $sFormat) {

    $condition = $this->create('condition', array($this, $this->create('not', array($test))));
    $text = $this->create('function', array($this, 't', array('Bad argument format')));
    $condition->addContent($this->createCall($this->getSelf(), 'throwException', null, array($text)));
  }*/
}