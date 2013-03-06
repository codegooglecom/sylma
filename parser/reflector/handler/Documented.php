<?php

namespace sylma\parser\reflector\handler;
use \sylma\core, sylma\parser\languages\common, sylma\dom, sylma\parser\reflector, sylma\storage\fs;

class Documented extends core\module\Domed implements reflector\documented {

  /**
   *
   * @var common\_window
   */
  protected $reflector;
  protected $sourceDir;
  protected $window;

  const PHP_TEMPLATE = '/#sylma/parser/languages/php/source.xsl';
  const WINDOW_ARGS = 'php';
  const BUILD_NS = 'http://2013.sylma.org/parser/reflector/builder';

  protected $bThrow = true;

  public function __construct($manager, fs\file $file, fs\directory $dir, core\argument $args = null) {

    $this->setManager($manager);

    $this->setFile($file);
    $this->setDocument($file->getDocument(array(), \Sylma::MODE_EXECUTE));

    $this->loadDefaultArguments();
    if ($args) $this->setArguments($args);
    $this->loadArguments($args);

    $this->setDirectory(__FILE__);
    $this->setSourceDirectory($dir);
  }

  protected function loadArguments(core\argument $arg = null) {

    if ($arg and $sArguments = $arg->read('arguments', null, false)) {

       $this->setArguments($sArguments);
    }
  }

  protected function setDocument(dom\handler $doc) {

    //$doc->registerNamespaces($this->getNS());

    $result = parent::setDocument($doc);

    if ($this->getDocument()->isEmpty()) {

      $this->throwException('Empty document');
    }

    return $result;
  }

  protected function setSourceDirectory(fs\directory $sourceDirectory) {

    $this->sourceDir = $sourceDirectory;
  }

  /**
   * Get the source file's directory
   * @return fs\directory
   */
  public function getSourceDirectory($sPath = '') {

    if (!$this->sourceDir) {

      $this->throwException('No source directory defined');
    }

    return $sPath ? $this->sourceDir->getDirectory($sPath) : $this->sourceDir;
  }

  /**
   * Get a file relative to the source file's directory
   * @param string $sPath
   * @return fs\file
   */
  public function getSourceFile($sPath = '') {

    return $this->getManager(static::FILE_MANAGER)->getFile($sPath, $this->getSourceDirectory());
  }

  protected function getClass(dom\handler $doc) {

    if (!$sResult = $doc->getRoot()->readAttribute('class', null, false)) {

      $sResult = $this->readArgument('cache/class');
    }

    return $sResult;
  }

  /**
   * @param common\_window $window
   */
  protected function setWindow(common\_window $window) {

    $this->window = $window;
  }

  /**
   *
   * @return common\_window
   */
  public function getWindow() {

    if (!$this->window) {

      $this->throwException('No window defined');
    }

    return $this->window;
  }

  protected function getTemplatePath() {

    if (!$sResult = $this->readArgument('template')) {

      $sResult = static::PHP_TEMPLATE;
    }

    return $sResult;
  }

  public function build() {
//$this->dsp($this->getArguments());
    $file = $this->getFile();
    $doc = $this->getDocument();

    $result = null;

    $reflector = $this->buildReflector($doc, $file);

    $window = $this->getWindow();
    $mContent = $this->parseReflector($reflector, $doc);

    $this->buildInstanciation($window, array($mContent));
    $arg = $window->asArgument();
      //echo $this->show($arg, false);

    $content = $arg->asDOM();

    if ($content) {

      if ($this->readArgument('debug/show')) {

        dsp($file->asToken());
        dsp($content);
      }

      $result = $this->loadTarget($doc, $file);

      $template = $this->getTemplate($this->getTemplatePath());

      $sContent = $template->parseDocument($content, false);
      $result->saveText($sContent);
    }

    return $result;
  }

  protected function loadTarget(dom\document $doc, fs\file $file) {

    if ($sTarget = $doc->getRoot()->readx('@build:target', array('build' => self::BUILD_NS), false)) {

      if ($sTarget{0} == '[') {

        switch (substr($sTarget, 1, -1)) {

          case 'current()' : $sTarget = (string) $file->getParent(); break;
          default : $this->throwException(sprintf('Cannot handler build option %s', $sTarget));
        }
      }

      $dir = $this->getControler('fs/editable')->getDirectory($sTarget);
      $result = $dir->getFile($file->getName() . '.php', fs\resource::DEBUG_EXIST);
    }
    else {

      $result = $this->getManager()->getCachedFile($file);
    }

    return $result;
  }

  protected function buildReflector(dom\document $doc, fs\file $file, common\_window $window = null) {

    try {

      $result = $this->createReflector();
      //$this->setReflector($reflector);

      $sInstance = $this->getClass($doc);

      if (!$window) {

        $window = $this->create('window', array($this, $this->getArgument(static::WINDOW_ARGS), $sInstance));
      }

      $this->setWindow($window);
    }
    catch (core\exception $e) {

      $e->addPath($file->asToken());

      if ($this->throwExceptions()) throw $e;
      else $e->save(false);

      $result = null;
    }

    return $result;
  }

  protected function createReflector() {

    $class = $this->getFactory()->findClass('elemented');

    return $this->create('elemented', array($this, null, $class));
  }

  protected function parseReflector(reflector\domed $reflector, dom\document $doc) {

    return $reflector->parseRoot($doc->getRoot());
  }

  protected function buildInstanciation(common\_window $window, array $aArguments) {

    $new = $window->createInstanciate($window->getSelf()->getInstance(), $aArguments);
    //$window->add($new);
    $window->setReturn($new);
  }

  public function throwExceptions($mValue = null) {

    if (!is_null($mValue)) $this->bThrow = $mValue;

    return $this->bThrow;
  }
}
