<?php

namespace sylma\core\argument\parser\compiler;
use sylma\core, sylma\parser, sylma\parser\languages\common, sylma\storage\fs;

class Main extends parser\compiler\Basic implements parser\compiler\documented {

  const WINDOW_ARGS = 'classes/php';
  const PHP_TEMPLATE = 'basic.xsl';

  public function __construct(core\factory $manager) {

    $this->setDirectory(__FILE__);
    $this->setControler($manager);

    $this->loadDefaultArguments();
  }

  public function build(fs\file $file, fs\directory $dir = null) {

    $window = $this->runReflector($file, $dir);

    if ($this->getControler()->readArgument('debug/show')) {

      $tmp = $this->create('document', array($window));
      echo '<pre>' . $file->asToken() . '</pre>';
      echo '<pre>' . str_replace(array('<', '>'), array('&lt;', '&gt'), $tmp->asString(true)) . '</pre>';
    }

    $result = $this->getCachedFile($file);
    $template = $this->getTemplate(self::PHP_TEMPLATE);

    $sContent = $template->parseDocument($window, false);
    $result->saveText($sContent);

    return $result;
  }

  public function buildInto(fs\file $file, fs\directory $dir, common\_window $window) {


  }

  protected function runReflector(fs\file $file, fs\directory $base = null) {

    $doc = $file->getDocument(array(), \Sylma::MODE_EXECUTE);
    $factory = $this->getControler();

    try {

      $reflector = $factory->create('reflector', array($factory, $doc, $base));

      $window = $factory->create('window', array($reflector, $factory->getArgument(self::WINDOW_ARGS), $factory->readArgument('classes\cached')));
      $reflector->setWindow($window);

      $result = $reflector->asDOM();
    }
    catch (core\exception $e) {

      $e->addPath($file->asToken());
      throw $e;
    }

    return $result;
  }

}
