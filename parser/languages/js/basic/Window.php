<?php

namespace sylma\parser\languages\js\basic;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\js, sylma\parser\languages\common;

class Window extends common\basic\Window implements js\window, core\stringable {

  const NS = 'http://www.sylma.org/parser/languages/js';

  const DEFAULT_TEMPLATE = '../source.xsl';
  const PHP_TEMPLATE = '../php.xsl';

  protected $aDefaultVariables = array(
    'sylma' => 'sylma',
    '$' => 'mootools\element',
    '$$' => 'mootools\elements',
  );

  public function __construct(reflector\elemented $controler, core\argument $args, $sClass = 'window') {

    $this->setDirectory(__FILE__);
    $this->loadDefaultArguments();

    $this->setControler($controler);
    $this->setArguments($args);
    $this->setNamespace(static::NS, 'self');

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

  protected function addContentUnknown($mVal) {

    $this->launchException('Not yet implemented');
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

  public function createVariable($sName, $mReturn) {

    return $this->create('variable', array($this, $sName, $this->loadReturn($mReturn)));
  }

  public function createCondition($test, $content = null) {

    $this->launchException('Not yet implemented');
  }

  public function addVar(common\argumentable $val, $sName = '') {

    $this->throwException('Not yet implemented');
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

  public function objAsDOM(common\_object $obj) {

    $result = null;

    $node = $obj->asArgument()->asDOM();

    $doc = $this->createDocument();
    $doc->addElement('window', null, array(), $this->getNamespace('self'));
    $doc->add($node->getx('self:items', $this->getNS(), false));

    if (!$doc->isEmpty()) {

      $result = $this->getTemplate(static::PHP_TEMPLATE)->parseDocument($doc);
    }

    return $result;

  }

  public function asString() {

    $doc = parent::asArgument()->asDOM();

    return $this->getTemplate(static::DEFAULT_TEMPLATE)->parseDocument($doc, false);
  }
}