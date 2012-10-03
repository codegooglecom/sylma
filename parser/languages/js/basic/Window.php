<?php

namespace sylma\parser\languages\js\basic;
use sylma\core, sylma\dom, sylma\parser, sylma\parser\languages\js, sylma\parser\languages\common;

class Window extends common\basic\Window implements js\window, core\stringable {

  const NS = 'http://www.sylma.org/parser/languages/js';
  const TEMPLATE = '/#sylma/parser/languages/js/source.xsl';

  protected $aDefaultVariables = array(
    'sylma' => 'sylma',
    '$' => 'mootools\element',
    '$$' => 'mootools\elements',
  );

  protected $aVariables = array();

  public function __construct(parser\reflector\domed $controler, core\argument $args, $sClass = 'window') {

    $this->setControler($controler);
    $this->setArguments($args);
    $this->setNamespace(self::NS, 'self');

    $this->self = $this->createVariable('window', $this->createGhost($sClass));
    $this->setScope($this);

    $this->loadDefaultVariables();
    //$node = $this->loadInstance('\sylma\dom\node', '/sylma/dom/node.php');
    //$this->setInterface($node->getInterface());

    //$this->sylma = $this->create('class-static', array('\Sylma'));
  }

  protected function loadDefaultVariables() {

    foreach ($this->aDefaultVariables as $sName => $mReturn) {

      $this->aVariables[$sName] = $this->createVariable($sName, $mReturn);
    }
  }

  public function createGhost($sInterface) {

    $result = $this->create('ghost', array($this, $sInterface));

    return $result;
  }

  public function createObject(array $aProperties = array()) {

    return $this->create('object', array($this, $aProperties));
  }

  public function createProperty($parent, $sName, $mReturn = null) {

    $result = $this->create('property', array($this, $parent, $sName, $this->loadReturn($mReturn)));

    return $result;
  }

  public function createFunction(array $aArguments = array(), $sContent = '', $mReturn = null) {

    return $this->create('function', array($this, $aArguments, $sContent, $this->loadReturn($mReturn)));
  }

  public function createCall(common\_function $function, array $aArguments = array(), $mReturn = null) {

    return $this->create('call', array($this, $function, $aArguments, $this->loadReturn($mReturn)));
  }

  public function createDeclare(common\_var $var) {

    return $this->create('declare', array($this, $var));
  }

  public function createCode($sValue) {

    return $this->createArgument(array('code' => $sValue));
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

  public function assignProperty($sPath, $mValue) {

    $val = $this->argToInstance($mValue);
    $aProperties = explode('.', $sPath);

    $obj = $this->getVariable(array_shift($aProperties));

    foreach ($aProperties as $sProperty) {

      $obj = $this->createProperty($obj, $sProperty);
    }

    $assign = $this->createAssign($obj, $val);
    $this->add($assign);
  }

  /**
   * Transform a PHP scalar value into a JS abstract object
   * @param mixed $mVar A PHP scalar (string, array, boolean, ...) content won't be copied
   * @return common\ghost
   */
  public function argToGhost($mVar) {

    $result = $this->createGhost(ucfirst(get_class($mVar)));

    return $result;
  }

  public function declareVar($sName, $mVal = null) {

    $var = $this->createVariable($sName);
    $declare = $this->createDeclare($var);

    if ($mVal) {

      $assign = $this->createAssign($declare, $this->argToInstance($mVal));
      $this->add($assign);
    }
    else {

      $this->add($declare);
    }

    return $var;
  }

  protected function parseArgument(dom\handler $doc) {

    $this->setDirectory(__FILE__);
    $this->loadDefaultArguments();

    $sResult = $this->getTemplate(self::TEMPLATE)->parseDocument($doc, false);

    return $sResult;
  }

  public function objAsString(common\_object $obj) {

    $sResult = '';
    $node = $obj->asArgument()->asDOM();
    $doc = $this->createDocument('window');

    $doc->add($node->queryx('self:item', $this->getNS(), false));

    if (!$doc->isEmpty()) $sResult = $this->parseArgument($doc);

    return $sResult;
  }

  public function asString() {

    $doc = parent::asArgument()->asDOM();

    return $this->parseArgument($doc);
  }
}