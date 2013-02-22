<?php

namespace sylma\parser\languages\php\basic\window;
use sylma\core, sylma\dom, sylma\parser\languages\php, sylma\parser\languages\common, sylma\storage\fs;

class Basic extends common\basic\Window implements php\window {

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

    $this->sylma =  $this->create('class', array($this, $this->tokenToInstance('\Sylma')->getInterface()));
  }

  protected function addContentUnknown($mVal) {

    if ($mVal instanceof common\_var) {

      $mVal->insert();
    }

    if (!$mVal instanceof dom\node && !$mVal instanceof php\basic\Condition) {

      $mVal = $this->create('line', array($this, $mVal));
    }

    $this->aContent[] = $mVal;
  }

  public function addControler($sName, $from = null) {

    if (!$from) $from = $this->getSylma();

    if (!array_key_exists($sName, $this->aControlers)) {

      $controler = $this->getControler($sName);
      $return = $this->tokenToInstance(get_class($controler));

      $call = $this->createCall($from, 'getControler', $return, array($sName));
      $this->aControlers[$sName] = $call->getVar();
    }

    return $this->aControlers[$sName];
  }

  public function getSylma() {

    return $this->sylma;
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

  public function createCall($obj, $sMethod, $mReturn, array $aArguments = array()) {

    $result = $this->create('call-method', array($this, $obj, $sMethod, $this->loadReturn($mReturn), $aArguments));

    return $result;
  }

  public function getStatic($sName) {

    return $this->createArgument(array(
      'class-static' => array(
        '@name' => $sName,
      )
    ));
  }

  public function callFunction($sName, common\_instance $return = null, array $aArguments = array()) {

    return $this->create('function', array($this, $sName, $return, $aArguments));
  }

  public function callClosure($closure, common\_instance $return, array $aArguments = array()) {

    return $this->create('call', array($this, $closure, $return, $aArguments));
  }

  public function createCondition($test, $content = null) {

    return $this->create('condition', array($this, $test, $content));
  }

  public function createVar(common\argumentable $val, $sName = '') {

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

    if (!$sName) $sName = $this->getVarName();

    return $this->create($sAlias, array($this, $return, $sName, $val));
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

      $result = $val->getVar();
    }
    else {

      $result = $this->createVar($val, $sName);
      $result->insert();
    }

    return $result;
  }

  public function createVariable($sName, $mReturn) {

    return $this->create('object-var', array($this, $this->loadReturn($mReturn), $sName));
  }

  public function createNot($mContent) {

    return $this->createArgument(array(
      'not' => array($mContent),
    ));
  }

  public function getVarName() {

    return 'var' . $this->getKey('var');
  }

  public function setInterface(php\basic\_Interface $interface) {

    $this->aInterfaces[$interface->getName()] = $interface;
  }

  public function getInterface($sName, fs\file $file = null) {

    if (!array_key_exists($sName, $this->aInterfaces)) {

      $this->aInterfaces[$sName] = $this->create('interface', array($this->getManager(), $sName, $file));
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