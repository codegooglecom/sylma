<?php

namespace sylma\dom\argument;
use sylma\core, sylma\dom;

abstract class Basic extends core\module\Controled {

  const NS = 'http://www.sylma.org/dom/argument';
  //const PREFIX_DEFAULT = 'self';

  private $document = null;
  private $schema = null;

  private $aOptions = array(); // cache array

  /**
   * See @method useAttribute()
   * @var boolean
   */
  protected $bAttribute = false;

  public function __construct(dom\document $doc = null, array $aNS = array()) {

    if ($doc) $this->build($doc, $aNS);
  }

  protected function build(dom\document $doc, array $aNS = array()) {

    $this->setDocument($doc);
    $this->setNamespaces($aNS);

    $this->loadDefaultNamespace($aNS);

    $this->setControler($this->getControler('dom'));

    // first element define default namespace & prefix

    if (!$this->getPrefix()) {

      if (!$sNamespace = $doc->getRoot()->getNamespace()) {

        if ($this->getNamespace()) {

          $sNamespace = $this->getNamespace();
        }
      }

      unset($this->aNamespaces['']);

      $this->setNamespace($sNamespace, 'self');
    }

    $this->registerNamespaces($this->getNS());
  }

  protected function loadDefaultNamespace(array $aNS) {

    foreach ($aNS as $sPrefix => $sNamespace) {

      if ($sPrefix == '') {

        $this->setNamespace($sNamespace);
        break;
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

  public function &locateValue(array &$aPath = array(), $bDebug = true, $bReturn = false) {

    $this->throwException('Not yet implemented');
  }

  public function merge($mArgument) {

    $this->throwException('Feature not implemented');
  }

  public function get($sPath = '', $bDebug = true, $bExpand = true) {

    $result = null;
    $dom = $this->getControler();

    $sRealPath = $this->parsePath($sPath);

    $result = $this->getDocument()->getx($sRealPath, array(), $bDebug);
    $this->useAttribute(false);

    $bElement = $result instanceof dom\element;

    if (!$bElement || ($result->hasChildren() && !$result->isComplex())) {

      if ($bDebug) $this->throwException(sprintf('Cannot use @path %s as complex element', $sPath));
    }
    else {

      if ($bExpand) $result = $this->buildChild($dom->create('handler', array($result)));
    }

    return $result;
  }

  public function buildChild(dom\document $doc) {

    $result = new static();
    $result->build($doc, array($this->getNamespace()));
    $result->setParent($this);

    return $result;
  }

  public function query($sPath = '', $bDebug = true) {

    $this->launchException('Not implemented');
  }

  public function read($sPath = '', $bDebug = true) {

    if ($sPath) {

      $sPath = $this->parsePath($sPath);

      if ($el = $this->getDocument()->getx($sPath, array(), $bDebug)) {

        if ($el->getType() === $el::ELEMENT) $sResult = $this->readElement($el);
        else $sResult = $el->getValue();
      }
      else {

        $sResult = '';
      }
    }
    else {

      $sResult = $this->readElement($this->getDocument()->getRoot());
    }

    $mResult = $this->parseAttribute($sResult);

    return $mResult;
  }

  public function getRoot() {

    return $this->getDocument()->getRoot()->getName();
  }

  protected function readElement(dom\element $el) {

    if ($el->isComplex()) {

      $this->launchException('Cannot read complex element');
    }

    return $el->read();
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

  public function add($mValue) {

    return $this->getDocument()->add($mValue);
  }

  public function normalize($bEmpty = false) {


  }

  public function asArray($bEmpty = false) {

    return array('DOM Argument cannot show content');
  }

  public function registerNamespaces(array $aNS) {

    $this->getDocument()->registerNamespaces($aNS);
  }

  protected function throwException($sMessage, $mSender = array(), $iOffset = 2) {

    //$mSender[] = $this->getNamespace();

    parent::throwException($sMessage, $mSender, $iOffset);
  }
}
