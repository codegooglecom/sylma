<?php

namespace sylma\dom\argument;
use sylma\core, sylma\dom;

require_once('core/module/Controled.php');
require_once('core/argument.php');

abstract class Basic extends core\module\Controled implements core\argument {

  const NS = 'http://www.sylma.org/dom/argument';
  const PREFIX_DEFAULT = 'self';

  private $document = null;
  private $schema = null;

  private $aOptions = array(); // cache array

  /**
   * See @method useAttribute()
   * @var boolean
   */
  protected $bAttribute = false;

  public function __construct(dom\document $doc, array $aNS = array()) {

    $this->setDocument($doc);
    $this->registerNamespaces($aNS);

    // first element define default namespace & prefix

    if (!$this->getPrefix()) {

      $root = $doc->getRoot();
      $this->registerNamespaces(array($root->getNamespace()));
    }
  }

  public function loadPrefix() {

    if (!$this->getPrefix()) {

      if ($sNamespace = $this->getNamespace()) {

        $this->setNamespace($sNamespace, self::PREFIX_DEFAULT);
      }
    }
  }

  public function setParent(core\argument $parent) {

    $this->parent = $parent;
  }

  public function getParent() {

    return $this->parent;
  }

  public function setDocument(dom\handler $document) {

    if ($document->isEmpty()) {

      $this->throwException(t('Cannot use empty doc as option\'s content'));
    }

    $this->document = $document;
  }

  public function getDocument() {

    return $this->document;
  }

  protected function parsePath($sPath) {

    $sResult = $sPath;

    $aPath = explode('/', $sPath);

    if (!$aPath) $aPath = array($sPath);

    foreach ($aPath as &$sSub) {

      $sSub = $this->parseName($sSub);
    }

    return implode('/', $aPath);
  }

  protected function parseName($sName) {

    $bAttribute = $sName{0} == '@';

    if ($bAttribute) $this->useAttribute(true);

    if (!$bAttribute && strpos($sName, ':') === false) {

      $sName = $this->getPrefix() . ':' . $sName;
    }

    return $sName;
  }

  /**
   * Return true if last path used attribute (@)
   * @param type $bVal
   * @return type
   */
  protected function useAttribute($bVal = null) {

    if (!is_null($bVal)) $this->bAttribute = $bVal;

    return $this->bAttribute;
  }

  protected function parseAttribute($sValue) {

    $mResult = $sValue;

    if ($this->useAttribute()) {

      if ($sValue === 'true') $mResult = true;
      else if ($sValue === 'false') $mResult = false;

      $this->useAttribute(false);
    }

    return $mResult;
  }

  public function validate() {

    $bResult = false;

    if (!$this->getSchema()) {

      $this->dspm(xt('Cannot validate, no schema defined'), 'warning');
    }
    else if (!$this->getDocument() || $this->getDocument()->isEmpty()) {

      $this->dspm(xt('Cannot validate, document empty or not defined'), 'warning');
    }
    else {

      $bResult = $this->getDocument()->validate($this->schema);
    }

    return $bResult;
  }

  public function get($sPath = '', $bDebug = true) {

    $result = null;
    $dom = $this->getControler('dom');

    $sRealPath = $this->parsePath($sPath);

    $result = $this->getDocument()->getx($sRealPath, array(), $bDebug);
    $this->useAttribute(false);

    if (!$result instanceof dom\element || ($result->hasChildren() && !$result->isComplex())) {

      $this->throwException(txt('Cannot use @path %s as complex element', $sPath));
    }

    $result = new Iterator($dom->create('handler', array($result)), $this->getNS());

    return $result;
  }

  public function read($sPath = '', $bDebug = true) {

    if ($sPath) {

      $sPath = $this->parsePath($sPath);
      $sResult = $this->getDocument()->readx($sPath, array(), $bDebug);
    }
    else {

      $sResult = $this->getDocument()->getRoot()->read();
    }

    $mResult = $this->parseAttribute($sResult);

    return $mResult;
  }

  // public function add($mValue = null) {

  public function set($sPath = '', $mValue = null) {

    $mResult = '';

    if ($eOption = $this->get($sPath)) {

      if ($mValue) $mResult = $eOption->set($mValue);
      else $mResult = $eOption->remove();
    }

    return $mResult;
  }

  public function normalize() {


  }

  public function asArray() {


  }

  public function registerNamespaces(array $aNS) {

    $this->setNamespaces($aNS);
    $this->loadPrefix();

    $this->getDocument()->registerNamespaces($this->getNS());
  }

  protected function throwException($sMessage, $mSender = array(), $iOffset = 2) {

    $mSender[] = $this->getNamespace();
    parent::throwException($sMessage, $mSender, $iOffset);
  }
}
