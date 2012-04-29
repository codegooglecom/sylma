<?php

namespace sylma\parser\action\php\basic;
use sylma\parser\action, sylma\core, sylma\dom, sylma\parser\action\php;

require_once('core/module/Domed.php');

require_once(dirname(__dir__) . '/_window.php');
require_once('core/controled.php');

class Window extends core\module\Domed implements php\_window, core\controled {

  const NS = 'http://www.sylma.org/parser/action/compiler';

  // Keyed by alias. ex : storage/fs
  protected $aControlers = array();

  // Keyed by namespace. ex : http://www.sylma.org/parser/action
  private $aParsers = array();

  // Keyed by file path. ex : #sylma/action/index.xsl
  private $aDependencies = array();

  protected $aInterfaces = array();

  // Indexed
  private $aContent = array();

  // $this reference object
  private $self;

  // static reference to class
  private $sylma;

  protected $sContext = self::CONTEXT_DEFAULT;

  protected $aScopes = array();

  protected $aKeys = array();

  public function __construct(action\compiler $controler, core\argument $args, $sClass) {

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

  public function createArgument($mArguments, $sNamespace = '') {

    return parent::createArgument($mArguments, $sNamespace);
  }

  public function setContext($sContext) {

    $this->sContext = $sContext;
  }

  public function getContext() {

    return $this->sContext;
  }

  public function add($mVal) {

    $this->getScope()->addContent($mVal);
  }

  public function checkContent($mVal) {

    if (!is_string($mVal) && !$mVal instanceof core\argumentable && !$mVal instanceof dom\node) {

      $formater = $this->getControler('formater');
      $this->throwException(sprintf('Cannot add %s in content', $formater->asToken($mVal)));
    }
  }

  public function addContent($mVal) {

    if (is_array($mVal)) {

      foreach ($mVal as $mChild) {

        $this->addContent($mChild);
      }
    }
    else {

      $this->checkContent($mVal);

      if (!$mVal instanceof dom\node && !$mVal instanceof php\basic\Condition) {

        $mVal = $this->create('line', array($this, $mVal));
      }

      $this->aContent[] = $mVal;
    }
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

  public function getSelf() {

    return $this->self;
  }

  public function getSylma() {

    return $this->sylma;
  }

  public function getVarName() {

    return 'var' . $this->getKey('var');
  }

  public function createCall(php\_object $obj, $sMethod, $mReturn, array $aArguments = array()) {

    if (is_string($mReturn)) {

      $return = $this->stringToInstance($mReturn);
    }
    else {

      $return = $mReturn;
    }

    $result = $this->create('call', array($this, $obj, $sMethod, $return, $aArguments));

    return $result;
  }

  public function createInsert($mVal, $sFormat = '', $iKey = null, $bTemplate = true) {

    if ($sFormat) {
      
      switch ($sFormat) {

        case 'dom' : $mVal = $this->convertToDOM($mVal); break;
        case 'txt' : $mVal = $this->convertToString($mVal); break;
      }
    }

    $result = $this->create('insert', array($this, $mVal, $iKey, $bTemplate));

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

  public function createTemplate(dom\node $node) {

    return $this->create('template', array($this, $node));
  }

  public function addVar(php\linable $val) {

    $var = $this->createVar($val);
    $var->insert();

    return $var;
  }

  public function createVar(php\linable $val) {

    if ($val instanceof Called) {

      $return = $val->getReturn();
    }
    else if ($val instanceof php\_var) {

      $return = $val->getInstance();
    }
    else if ($val instanceof dom\node) {

      $return = $this->getInterface('\sylma\dom\node');
    }
    else {

      $return = $val;
    }

    if ($return instanceof php\_object) $sAlias = 'object-var';
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

  public function getScope() {

    if (!$this->aScopes) {

      $this->throwException(t('Cannot get scope, no scope defined'));
    }

    return $this->aScopes[count($this->aScopes) - 1];
  }

  public function setScope(php\scope $scope) {

    $this->aScopes[] = $scope;
  }

  public function stopScope() {

    if (!$this->aScopes) {

      $this->throwException(t('Cannot stop scope, no scope defined'));
    }

    return array_pop($this->aScopes);
  }

  public function convertToString($val, $iMode = 0) {

    $result = null;

    if ($val instanceof php\_scalar) {

      if ($val instanceof php\_var) $instance = $val->getInstance();
      else $instance = $val;

      if ($instance instanceof php\_scalar) {

        $controler = $this->getControler();
        $bString = $controler->useTemplate() || $controler->useString();

        if ($bString && $instance instanceof php\basic\instance\_Array) {

          $aContent = array();
          foreach($instance as $sub) {

            $aContent[] = $this->convertToString($sub);
          }

          $val = $this->createString($aContent);
        }

        $result = $val;
      }
      else {

        $this->throwException(sprintf('Cannot convert scalar value %s to string', get_class($instance)));
      }
    }
    else if ($val instanceof php\basic\Called) {

      $val = $val->getVar();
      $result = $this->convertToString($val);
    }
    else if ($val instanceof php\_object) {

      $interface = $val->getInstance()->getInterface();

      if (!$interface->isInstance('\sylma\core\stringable')) {

        $this->throwException(sprintf('Cannot convert object %s to string', $interface->getName()));
      }

      $result = $this->createCall($this->getSelf(), 'loadStringable', 'php-string', array($val, $iMode));
    }
    else if ($val instanceof dom\node) {

      $result = $this->argToInstance($val);
    }
    else if ($val instanceof php\basic\Template) {

      $result = $val;
    }
    else {

      $formater = $this->getControler('formater');
      $this->throwException(sprintf('Cannot convert %s to string', $formater->asToken($val)));
    }

    return $result;
  }

  public function convertToDOM($val) {

    if (is_array($val)) {

      // concat

      foreach ($val as $mSub) {

        $aResult[] = $this->convertToDOM($mSub);
      }

      $result = $this->createString($aResult);
    }
    else if ($val instanceof CallMethod) {

      $result = $this->convertToDOM($val->getVar());
    }
    else if ($val instanceof php\_object) {

      $interface = $val->getInstance()->getInterface();

      if ($interface->isInstance('\sylma\dom\node')) {

        //$result = $this->convertToString($val);
        $result = $val;
      }
      else if ($interface->isInstance('\sylma\core\argumentable')) {

        $call = $this->createCall($this->getSelf(), 'loadArgumentable', '\sylma\dom\node', array($val));

        $result = $call;
      }
      else if ($interface->isInstance('\sylma\dom\domable')) {

        $call = $this->createCall($this->getSelf(), 'loadDomable', '\sylma\dom\node', array($val));

        $result = $call;
      }
      else {

        $this->throwException(sprintf('Cannot add @class %s', $interface->getName()));
      }
    }
    else if ($val instanceof php\_scalar) {

      $result = $this->convertToString($val);
    }
    else if ($val instanceof dom\node) {

      $result = $var;
    }
    else {

      $frm = \Sylma::getControler('formater');
      $this->throwException(sprintf('Cannot insert %s', $frm->asToken($val)));
    }

    return $result;
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
      else if ($mVar instanceof php\_instance ||
          $mVar instanceof php\basic\Called ||
          $mVar instanceof php\_var) {

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

  public function getKey($sPrefix) {

    if (array_key_exists($sPrefix, $this->aKeys)) {

      $this->aKeys[$sPrefix]++;
    }
    else {

      $this->aKeys[$sPrefix] = 1;
    }

    return $this->aKeys[$sPrefix];
  }

  /*public function validateFormat(php\_var $var, $sFormat) {

    $condition = $this->create('condition', array($this, $this->create('not', array($test))));
    $text = $this->create('function', array($this, 't', array('Bad argument format')));
    $condition->addContent($this->createCall($this->getSelf(), 'throwException', null, array($text)));
  }*/

  public function throwException($sMessage, $mSender = array(), $iOffset = 2) {

    return parent::throwException($sMessage, $mSender, $iOffset);
  }

  public function asArgument() {

    $interface = $this->getControler()->getInterface();

    $result = $this->createArgument(array(
      'window' => array(
        '@extends' => $interface->getNamespace('php') . '\\' . $interface->getName(),
      ),
    ), self::NS);

    $result->get('window')->mergeArray($this->aContent);

    return $result;
  }
}