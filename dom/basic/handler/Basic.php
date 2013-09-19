<?php

namespace sylma\dom\basic\handler;
use sylma\dom, sylma\storage\fs, sylma\core;

/**
 * Existenz of this class mainly due to https://bugs.php.net/bug.php?id=28473
 * Allow too extension of document methods with others arguments
 */
abstract class Basic extends core\module\Managed {

  /**
   * See @method setFile()
   * @var fs\file
   */
  private $file;

  protected $aClasses = array();

  /**
   * See @method getMode() for details
   * @var integer
   */
  private $iMode = null;

  public function __construct($mContent = '', $iMode = \Sylma::MODE_READ, array $aNamespaces = array()) {

    $manager = \Sylma::getManager('dom');

    $this->setManager($manager);
    $this->setMode($iMode);

    $this->setDocument($manager->create('document'));

    $this->registerClasses();
    $this->registerNamespaces($aNamespaces);

    $this->setFragment($this->getDocument()->createDocumentFragment());
    $this->set($mContent);
  }

  public function getType() {

    return self::HANDLER;
  }

  public function getDocument() {

    return $this->document;
  }

  public function getParent() {

    return null;
  }

  protected function setDocument(dom\document $doc) {

    $doc->setHandler($this);
    $this->document = $doc;
  }

  private function setMode($iMode) {

    $aModes = array(\Sylma::MODE_EXECUTE, \Sylma::MODE_WRITE, \Sylma::MODE_READ);

    if (in_array($iMode, $aModes)) $this->iMode = $iMode;
  }

  public function getMode() {

    return $this->iMode;
  }

  protected function startString($sValue) {

    $bResult = false;

    if ($sValue{0} == '/') {

      $fs = \Sylma::getManager('fs');
      $file = $fs->getFile($sValue);

      $this->setFile($file);
      $bResult = $this->loadFile();
    }
    else if ($sValue{0} == '<') {

      $bResult = $this->loadText($sValue);
    }
    else {

      $bResult = (bool) $this->set($this->createElement($sValue, null, array(), $this->getNamespace()));
    }

    return $bResult;
  }

  public function createElement($sName, $mContent = '', array $aAttributes = array(), $sNamespace = null) {

    $doc = $this->getDocument();

    if (!$sName) $this->throwException(t('Empty value cannot be used as element\'s name'));

    try {

      if ($sNamespace) {

        // always add prefix if namespace, see @method dom\basic\Document::importNode() for more details
        if (!strpos($sName, ':')) {

          $sName = $this->generateName($sName, $sNamespace);
        }

        $el = $doc->createElementNS($sNamespace, $sName);
      }
      else {

        $el = $doc->createElement($sName);
      }
    }
    catch (\DOMException $e) {

      $this->launchException('DOM exception : ' . $e->getMessage(), get_defined_vars());
    }

    if ($mContent) {

      $el->set($mContent);
    }

    if ($aAttributes) {

      $el->setAttributes($aAttributes);
    }

    return $el;
  }

  public function createCData($mContent) {

    return $this->getDocument()->createCDATASection($mContent);
  }

  public function registerNamespaces(array $aNS = array()) {

    $this->setNamespaces($aNS);
  }

  public function registerClasses(core\argument $settings = null) {

    $aClasses = $this->getControler()->getClasses($settings);

    foreach ($aClasses as $sOrigin => $sReplacement) {

      $this->getDocument()->registerNodeClass($sOrigin, $sReplacement);
    }
  }

  public function setFile(fs\file $file) {

    $this->file = $file;
  }

  public function getFile() {

    return $this->file;
  }

  public function loadFile($bSecured = true) {

    $sContent = '';

    if (!$this->getFile()) {

      $this->throwException(t('No file associated'));
    }

    if ($bSecured) {

      if ($this->getMode() == \Sylma::MODE_READ) $sContent = $this->getFile()->read();
      else if ($this->getMode() == \Sylma::MODE_EXECUTE) $sContent = $this->getFile()->execute();
    }
    else {

      $sContent = $this->getFile()->freeRead();
    }

    return $this->loadText($sContent);
  }

  public function loadText($sContent) {

    try {

      $bResult = $this->document->loadXML($sContent);
    }
    catch (\DOMException $e) {

      $this->throwException($e->getMessage());
    }
    catch (core\exception $e) {

      $this->throwException($e->getMessage());
    }

    return $bResult;
  }

  public function mergeNamespaces(array $aNamespaces = array()) {

    return parent::mergeNamespaces($aNamespaces);
  }

  protected function parseNamespaces($sContent) {

    $reader = new \XMLReader;
    $reader->XML($sContent);

    $aNS = $this->lookupNamespaces($reader);
    $this->registerNamespaces($aNS);
  }

  private function lookupNamespaces(\XMLReader $reader) {

    $aNS = array();

    while ($reader->read()) {

      switch ($reader->nodeType) {

        // case \XMLReader::NONE : break;
        case \XMLReader::ELEMENT :

          $aNS[$reader->prefix] = $reader->namespaceURI;

          if($reader->hasAttributes) {

            while($reader->moveToNextAttribute()) {

              $aNS[$reader->prefix] = $reader->namespaceURI;
            }
          }

          if (!$reader->isEmptyElement) {

            $aNS = array_merge($aNS, $this->lookupNamespaces($reader));
          }

        break;
        // case \XMLReader::ATTRIBUTE : break;
        // case \XMLReader::TEXT : break;
        case \XMLReader::END_ELEMENT : //dspf($reader->expand(new \XML_Element)); break 2;
        // case \XMLReader::XML_DECLARATION : break;
      }
    }

    return $aNS;
  }

  public function asToken() {

    if ($this->getFile()) $sResult = '@file ' . $this->getFile();
    else $sResult = '@file [unknown]';

    return $sResult;
  }

  public function asArgument() {

    $dom = $this->getControler();
    $content = null;

    if (!$this->isEmpty()) {

      // copy handler for display updates (add of whitespaces)
      $copy = $dom->create('handler', array($this));
      $copy->getRoot()->prepareHTML();

      $content = $copy->getContainer()->saveXML(null);
    }

    return $dom->createArgument(array(
        'handler' => array(
            '@class' => get_class($this),
            'content' => $this->getDocument(),
        ),
    ), $dom->getNamespace());
  }

  public function elementAsString(dom\node $el = null, $iMode = 0) {

    if ($iMode & dom\handler::STRING_INDENT) {

      if (!$el) $el = $this->getRoot(false);

      if ($el) {

        $doc = new static($el);
        $doc->getRoot()->prepareHTML();

        $container = $doc->getContainer();
        $sResult = $el ? $container->saveXML($doc->getRoot()) : $container->saveXML();
        //echo $sResult;
      }
      else {

        $sResult = '';
      }
    }
    else {

      if (!$sResult = $this->getContent()) {

        $container = $this->getContainer();

        if ($el) $sResult = $container->saveXML($el);
        else if ($this->getRoot(false)) $sResult = $container->saveXML();
        else $sResult = '';
      }

      $sResult = trim($sResult);
    }

    return $sResult;
  }

  public function asString($iMode = 0) {

    if ($iMode & dom\handler::STRING_HEAD) $sResult = $this->elementAsString(null, $iMode);
    else $sResult = $this->elementAsString($this->getRoot(false), $iMode);

    return $sResult;
  }

  public function throwException($sMessage, $mSender = array(), $iOffset = 0, array $aVars = array()) {

    $mSender = (array) $mSender;

    $mSender[] = '@namespace ' . dom\handler::NS;
    $mSender[] = $this->asToken();

    \Sylma::throwException($sMessage, $mSender, $iOffset + 2, $aVars);
  }

  public function saveFile(fs\editable\file $file, $bFormat = false) {

    if ($this->isEmpty()) {

      $this->throwException(sprintf('You cannot save empty document in %s', $file->asToken()));
    }

    if ($bFormat) $this->getRoot()->prepareHTML();
    $file->saveText($this->asString(dom\handler::STRING_HEAD));
  }

  public function dsp() {

    dspm($this->asString());
  }
}