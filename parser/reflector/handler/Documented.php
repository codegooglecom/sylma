<?php

namespace sylma\parser\reflector\handler;
use \sylma\core, sylma\parser\languages\common, sylma\dom, sylma\parser\reflector, sylma\storage\fs;

class Documented extends core\module\Domed implements reflector\documented {

  /**
   *
   * @var common\_window
   */
  private $window;
  private $reflector;
  protected $sourceDir;

  public function __construct($manager, dom\handler $doc, fs\directory $dir) {

    $this->setManager($manager);
    $this->setDocument($doc);

    $this->setSourceDirectory($dir);
  }

  public function setReflector(reflector\elemented $reflector) {

    $this->reflector = $reflector;
  }

  public function getReflector() {

    return $this->reflector;
  }

  /**
   * @param common\_window $window
   */
  public function setWindow(common\_window $window) {

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

  /**
   *
   * @param $doc
   * @return array
   */
  protected function parseDocument(dom\document $doc) {

    //$reflector = $this->getManager()->create('elemented', array());
    //$this->setReflector($reflector);

    $reflector = $this->getReflector();

    return $reflector->parseRoot($doc->getRoot());
  }

/*
  protected function buildContainer(php\window $window) {

    $switch = $window->createSwitch();
    $switch->setCase(self::MODE_DEFAULT);

    $window->add($switch);
    $window->setScope($switch);
  }
*/

  /**
   *
   * @return array
   */
  protected function build() {

    $doc = $this->getDocument();

    if ($doc->isEmpty()) {

      $this->throwException('Empty document');
    }

    $doc->registerNamespaces($this->getNS());

    $window = $this->getWindow();
    //$this->buildContainer($window);

    $mContent = $this->parseDocument($doc);
    $this->buildInstanciation($window, array($mContent));
  }

  protected function buildInstanciation(common\_window $window, array $aArguments) {

    $new = $window->createInstanciate($window->getSelf()->getInstance(), $aArguments);
    //$window->add($new);
    $window->setReturn($new);
  }

  protected function setSourceDirectory(fs\directory $sourceDirectory) {

    $this->sourceDir = $sourceDirectory;
  }

  /**
   * Get the source file's directory
   * @return fs\directory
   */
  public function getSourceDirectory() {

    return $this->sourceDir;
  }

  public function getNamespace($sPrefix = null) {

    return $this->getReflector()->getNamespace($sPrefix);
  }

  public function asDOM() {

    $this->build();

    $arg = $this->getWindow()->asArgument();
    //echo $this->show($arg, false);

    return $arg->asDOM();
  }
}
