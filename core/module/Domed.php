<?php

namespace sylma\core\module;
use \sylma\dom, \sylma\core, sylma\storage\fs;

/**
 * Settings in DOM Document with @method getSettings() (global settings) and @method getOptions() (context settings)
 * Main directory relative calls (actions, documents, templates)
 */
abstract class Domed extends Filed {

  /**
   * @var dom\argument
   */
  private $options = null;  // contextual settings

  /**
   * @var dom\handler
   */
  protected $document = null;

  protected static $sArgumentClass = 'sylma\core\argument\Readable';

  const ARGUMENTS = 'domed.yml';

  const DOM_CONTROLER = 'dom';
  const DOM_DOCUMENT_ALIAS = 'handler';
  const DOM_ARGUMENT_ALIAS = 'argument';

  protected function loadDefaultArguments() {

    $this->setArguments(array(
    'classes' => array(
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
      )));
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

    $dom = \Sylma::getControler(static::DOM_CONTROLER);
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

  protected function createPath($sPath, array $aArguments = array()) {

    $path = $this->create('path', array($sPath, $this->getDirectory('', false), $aArguments));

    return $path;
  }

  protected function getScript($sPath, array $aArguments = array(), array $aContexts = array(), array $aPosts = array()) {

    if (strpos($sPath, '.') !== false) {

      $file = $this->getFile($sPath);
    }
    else {

      $path = $this->createPath($sPath, $aArguments);
      $aArguments = $path->getArguments();
      $file = $path->asFile();
    }

    $aArguments = array(
      'arguments' => $aArguments ? $this->createArgument($aArguments) : null,
      'contexts' => $aContexts ? $this->createArgument($aContexts) : null,
      'post' => $aPosts ? $this->createArgument($aPosts) : null,
    );

    return $this->getScriptFile($file, $aArguments);
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

/*
  protected function setOptions(dom\document $options, dom\document $schema = null, $aNS = array()) {

    $this->options = $this->create('options', array($options, $schema, $this->mergeNamespaces($this->getNS(), $aNS)));

    return $this->getOptions();
  }

  protected function getOptions() {

    return $this->options;
  }
*/
  /**
   * Return a setting result from @interface SettingsInterface object set with @method setOptions()
   *
   * @param string $sPath The path to the value wanted
   * @param mixed $mDefault The default value to return if no value is found
   * @param boolean $bDebug If set to TRUE, exceptions launched in class will be thrown.
   *
   * @return mixed The value found at the location of @param $sPath or null if not found
   */
  protected function getOption($sPath, $mDefault = null, $bDebug = false) {

    $result = null;

    if ($this->getOptions()) $result = $this->getOptions()->get($sPath, $bDebug);
    return isset($result) ? $result : $mDefault;
  }

  /**
   * Return a string formated option read with @method getOptions()
   *
   * @param string $sPath The path to the value wanted
   * @param mixed The default value to return if no value is found
   * @param boolean If set to TRUE, an @interface SylmaExceptionInterface object will be sent
   *
   * @return string|null The value found at the location of @param $sPath or null if not found
   */
  protected function readOption($sPath, $mDefault = null, $bDebug = false) {

    $sResult = null;

    if ($this->getOptions()) $sResult = $this->getOptions()->read($sPath, $bDebug);
    return $sResult ? $sResult : $mDefault;
  }

  public function getFullPrefix() {

    return $this->getPrefix() ? $this->getPrefix().':' : '';
  }
}

