<?php

namespace sylma\core\module;
use \sylma\dom, \sylma\core, sylma\storage\fs;

/**
 * Settings in DOM Document with @method getSettings() (global settings) and @method getOptions() (context settings)
 * Main directory relative calls (actions, documents, templates)
 */
abstract class Domed extends Filed {

  const ARGUMENTS = 'domed.yml';

  const DOM_CONTROLER = 'dom';
  const DOM_DOCUMENT_ALIAS = 'handler';
  const DOM_ARGUMENT_ALIAS = 'argument';

  /**
   * @var dom\argument
   */
  private $options = null;  // contextual settings

  /**
   * @var dom\handler
   */
  protected $document = null;

  protected static $sArgumentClass = 'sylma\core\argument\Readable';
  protected static $sFactoryClass = '\sylma\core\factory\Cached';

  protected $aDefaultArguments = array('classes' => array(
    'action' => array(
      'name' => '\sylma\parser\action\handler\Basic'),
    'template' => array(
      'name' => '\sylma\parser\xslt\Processor'),
    'options' => array(
      'name' => '\sylma\dom\argument\Iterator'),
    'document' => array(
      'name' => '\sylma\dom\basic\handler\Rooted'),
    'path' => array(
      'name' => '\sylma\core\request\Basic'),
  ));

  protected function loadDefaultArguments() {

    $this->setArguments($this->aDefaultArguments);
  }

  protected function loadDefaultSettings() {

    $this->setSettings($this->aDefaultArguments);
  }

  /*protected function createArgument($mArguments, $sNamespace = '') {

    if ($mArguments instanceof dom\document) {

      $dom = $this->getControler(self::DOM_CONTROLER);
      $aNS = $sNamespace ? array($sNamespace) : array();

      $result = $dom->create(self::DOM_ARGUMENT_ALIAS, array($mArguments, $aNS));
    }
    else {

      $result = parent::createArgument($mArguments, $sNamespace);
    }

    return $result;
  }*/

  /**
   * Create a DOM document with content sent to it
   * @return \sylma\dom\handler
   */
  protected function createDocument($mContent = null) {

    $dom = $this->getManager(static::DOM_CONTROLER);
    $result = $dom->create(static::DOM_DOCUMENT_ALIAS);

    $result->registerNamespaces($this->getNS());
    if ($mContent) $result->set($mContent);

    return $result;
  }

  protected function readAction($sPath, array $aArguments = array()) {

    $controler = $this->getControler('action');

    return $controler->getAction($sPath, $aArguments, $this->getDirectory());
  }

  protected function getScriptFile(fs\file $file, array $aArguments = array()) {

    return $this->getManager(self::PARSER_MANAGER)->load($file, $aArguments);
  }

  /**
   *
   * @param type $sPath
   * @param array $aArguments
   * @return \sylma\core\request
   */
  protected function createPath($sPath, array $aArguments = array()) {

    $path = $this->create('path', array($sPath, $this->getDirectory('', false), $aArguments));

    return $path;
  }

  protected function buildScriptArguments(array $aArguments = array(), array $aContexts = array(), array $aPosts = array()) {

    return array(
      'arguments' => $aArguments ? $this->createArgument($aArguments) : null,
      'contexts' => $aContexts ? $this->createArgument($aContexts) : null,
      'post' => $aPosts ? $this->createArgument($aPosts) : null,
    );
  }

  protected function getActionContexts() {

    return $this->getManager(self::PARSER_MANAGER)->getContext('action/current')->getContexts();
  }

  protected function getScript($sPath, array $aArguments = array(), array $aContexts = array(), array $aPosts = array()) {

    if (strpos($sPath, '.') !== false) {

      $file = $this->getFile($sPath);
    }
    else {

      $path = $this->createPath($sPath, $aArguments);
      $aArguments = $path->getArguments()->query();
      $file = $path->asFile();
    }

    $aArguments = $this->buildScriptArguments($aArguments, $aContexts, $aPosts);

    switch ($file->getExtension()) {

      case 'vml' :
      case 'xml' :

        $result = $this->getScriptFile($file, $aArguments);
        break;

      case 'eml' :

        $result = $this->readAction((string) $path, $aArguments);
        break;

      default :

        $this->launchException("Unknown script extension : {$file->getExtension()}");
    }

    return $result;
  }

  /**
   * Load an XSL Template from a path relative to the module's directory
   *
   * @param string $sPath The path to the template, relative to the module's directory
   * @return \sylma\parser\xslt\Handler|null The loaded template, or null if not found/valid
   */
  protected function getTemplate($sPath) {

    $result = null;
    $file = $this->getFile($sPath);

    if ($file) {

      $result = $this->create('template', array((string) $file, \Sylma::MODE_EXECUTE));
    }

    return $result;
  }

  /**
   * Load a DOM Document from a path relative to the module's directory or self document property if no path is sent
   *
   * @param string $sPath The path to the document, relative to the module's directory
   * @return \sylma\dom\document|null The loaded document, the document property if path is not sent (or empty), or null if not found/valid
   */
  protected function getDocument($sPath = '', $bDebug = true) {

    $doc = null;

    if ($sPath) {

      if ($file = $this->getFile($sPath, $bDebug)) {

        $doc = $file->getDocument($this->getNS());
      }
    }
    else {

      $doc = $this->document;

      if (!$doc && $bDebug) {

        $this->throwException('No document associated to this object');
      }
    }

    return $doc;
  }

  protected function setDocument(dom\handler $doc) {

    $doc->registerNamespaces($this->getNS());
    $this->document = $doc;
  }

  protected function createOptions($mContent) {

    if (!$mContent) {

      $this->launchException('A document is needed to build options');
    }

    if (is_string($mContent)) {

      $result = $this->create('options', array($this->createDocument($mContent), array($this->getNamespace())));
    }
    else {

      $result = $this->create('options', array($mContent, $this->getNS()));
    }

    return $result;
  }

  public function getFullPrefix() {

    return $this->getPrefix() ? $this->getPrefix().':' : '';
  }
}

