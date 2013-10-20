<?php

namespace sylma\core\module;
use \sylma\dom, \sylma\core;

require_once('Filed.php');

/**
 * Settings in DOM Document with @method getSettings() (global settings) and @method getOptions() (context settings)
 * Main directory relative calls (actions, documents, templates)
 */
abstract class Domed extends Filed {

  private $options = null;  // contextual settings

  //protected static $sArgumentClass = 'sylma\core\argument\Domed';
  //protected static $sArgumentFile = 'core/argument/Domed.php';

  const ARGUMENTS = 'domed.yml';

  const DOM_CONTROLER = 'dom';
  const DOM_DOCUMENT_ALIAS = 'handler';
  const DOM_ARGUMENT_ALIAS = 'argument';

  const FILE_CONTROLER = 'fs';

  protected function loadDefaultArguments() {

    $fs = \Sylma::getControler(self::FILE_CONTROLER);

    $dir = $fs->extractDirectory(__file__);
    $this->setArguments($dir . '/' . self::ARGUMENTS);
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
   */
  protected function createDocument($mContent = '') {

    $dom = \Sylma::getControler(self::DOM_CONTROLER);

    return $dom->create(self::DOM_DOCUMENT_ALIAS, array($mContent));
  }

  protected function readAction($sPath, array $aArguments = array()) {

    $controler = $this->getControler('action');

    return $controler->getAction($sPath, $aArguments, $this->getDirectory());
  }

  /**
   * Load an XSL Template from a path relative to the module's directory
   *
   * @param string $sPath The path to the template, relative to the module's directory
   * @return null|DOMDocument The loaded template, or null if not found/valid
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
   * Load a DOM Document from a path relative to the module's directory
   *
   * @param string $sPath The path to the document, relative to the module's directory
   * @param integer $iMode The load mode (READ, WRITE, EXECUTION)
   *
   * @return dom\document|null The loaded document, or null if not found/valid
   */
  protected function getDocument($sPath, $iMode = \Sylma::MODE_EXECUTE) {

    $doc = null;

    if ($file = $this->getFile($sPath)) {

      $doc = $file->getDocument(array(), $iMode);
    }

    return $doc;
  }

  protected function setOptions(dom\document $options, dom\document $schema = null, $aNS = array()) {

    $this->options = $this->create('options', array($options, $schema, $this->mergeNamespaces($this->getNS(), $aNS)));

    return $this->getOptions();
  }

  protected function getOptions() {

    return $this->options;
  }

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
