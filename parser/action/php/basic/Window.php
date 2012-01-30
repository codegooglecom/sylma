<?php

namespace sylma\parser\action\php\basic;
use \sylma\parser\action, \sylma\core, \sylma\dom, \sylma\parser\action\php;

require_once('core/module/Argumented.php');
require_once(dirname(__dir__) . '/_window.php');
require_once('core/controled.php');

class Window extends core\module\Filed implements php\_window, core\controled {

  const NS = 'http://www.sylma.org/parser/action/compiler';

  // Keyed by alias. ex : storage/fs
  protected $aControlers = array();

  // Keyed by namespace. ex : http://www.sylma.org/parser/action
  private $aParsers = array();

  // Keyed by file path. ex : #sylma/action/index.xsl
  private $aDependencies = array();

  // Indexed
  private $aContent = array();

  // $this reference object
  private $self;

  // static reference to class
  private $sylma;

  public $aScopes = array();

  protected static $varCount = 0;

  public function __construct(action\Domed $controler, core\argument $args, $sClass) {

    $this->setControler($controler);
    $this->setArguments($args);
    $this->setNamespace(self::NS);

    $self = $this->loadInstance($sClass);

    $this->self = $this->create('object-var', array($this, $self, 'this'));
    $this->setScope($this);
    //$this->sylma = $this->create('class-static', array('\Sylma'));
  }

  public function createArgument($mArguments, $sNamespace = '') {

    return parent::createArgument($mArguments, $sNamespace);
  }

  public function add($mVal) {

    $this->getScope()->addContent($mVal);
  }

  public function checkContent($mVal) {

    if (!is_string($mVal) && !$mVal instanceof core\argumentable) {

      $formater = $this->getControler('formater');
      $this->throwException(txt('Cannot add %s in content', $formater->asToken($mVal)));
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

      $line = $this->create('line', array($this, $mVal));
      $this->aContent[] = $line;
    }
  }

  public function addControler($sName) {

    $this->aControlers[] = $sName;
  }

  public function getSelf() {

    return $this->self;
  }

  public function getSylma() {

    return $this->sylma;
  }

  public function getVarName() {

    self::$varCount++;
    return 'var' . self::$varCount;
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

  public function createInsert(core\argumentable $val) {

    //if ($val instanceof dom\node) $result = $val;
    $result = $this->create('insert', array($this, $val));

    return $result;
  }

  public function getScope() {

    if (!$this->aScopes) {

      $this->throwException(t('Cannot get scope, no scope defined'));
    }

    return array_last($this->aScopes);
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

  public function convertToString($val) {

    $result = null;

    if ($val instanceof php\_scalar) {

      if ($val instanceof php\_var) $instance = $val->getInstance();
      else $instance = $val;
      
      if ($instance instanceof php\basic\instance\_String || $instance instanceof php\basic\_String) {

        $result = $val;
      }
      else {

        $this->throwException(txt('Cannot convert scalar value %s to string', get_class($val)));
      }
    }
    else if ($val instanceof php\basic\Called) {

      $val = $val->getVar();
      $result = $this->convertToString($val);
    }
    else if ($val instanceof php\_object) {

      $interface = $val->getInstance()->getInterface();

      if (!$interface->isInstance('\sylma\core\stringable')) {

        $this->throwException(txt('Cannot convert object %s in string', get_class($val)));

      }

      $result = $this->createCall($this->getSelf(), 'loadStringable', 'php-string', array($val));
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
      case 'double' :

        if (is_null($mVar)) $mVar = 0;
        $result = $this->create('numeric', array($this, $mVar));

      break;

      case 'string' :

        if (is_null($mVar)) $mVar = '';
        $result = $this->create('string', array($this, $mVar));

      break;

      case 'array' :

        if (is_null($mVar)) $mVar = array();
        $result = $this->create('array', array($this, $mVar));

      break;

      default :

        $this->throwException(txt('Unkown scalar type as argument : %s', $sFormat));
    }

    return $result;
  }

  public function loadInstance($sName, $sFile = '') {

    $interface = $this->create('interface', array($this, $sName, $sFile));
    $result = $this->create('object', array($this, $interface));

    return $result;
  }

  public function argToInstance($mVar) {

    $arg = null;
    $sFormat = gettype($mVar);

    switch ($sFormat) {

      case 'object' :

        if ($mVar instanceof php\_instance || $mVar instanceof php\basic\Called || $mVar instanceof php\_var) $arg = $mVar;
        else $arg = $this->loadInstance(get_class($mVar));

      break;

      case 'resource' :
      case 'NULL' :

        $arg = $this->create('null', array($this));

      break;

      default :

        $arg = $this->stringToScalar($sFormat, $mVar);
    }

    return $arg;
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

    $aControlers = array();

    foreach ($this->aControlers as $sControler) {

      $sName = '$controler' . ucfirst(str_replace('/', '_', $sControler));

      // $controlerXX_X = \Sylma::getControler('xx/x');

      $var = $this->create('var', array($sName));
      $call = $this->create('call-static', array($this->getSylma(), 'getControler', array($sControler)));

      $aControlers[] = $this->create('assign', array($var, $call));
    }

    $interface = $this->getControler()->getInterface();

    $result = $this->createArgument(array(
      'window' => array(
        '@extends' => $interface->getNamespace('php') . '\\' . $interface->getName(),
        $aControlers,
      ),
    ), self::NS);

    $result->get('window')->mergeArray($this->aContent);

    return $result;
  }
}