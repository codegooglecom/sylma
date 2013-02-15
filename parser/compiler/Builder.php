<?php

namespace sylma\parser\compiler;
use sylma\core, sylma\parser, sylma\storage\fs, sylma\dom, sylma\parser\languages\php;

class Builder extends Manager {

  const PHP_TEMPLATE = '/#sylma/parser/languages/php/source.xsl';
  const WINDOW_ARGS = 'classes/php';

  protected $bThrow = true;

  protected function getClass(dom\handler $doc) {

    if (!$sResult = $doc->getRoot()->readAttribute('class', null, false)) {

      $sResult = $this->readArgument('cache/class');
    }

    return $sResult;
  }

  protected function getTemplatePath() {

    if (!$sResult = $this->readArgument('template')) {

      $sResult = static::PHP_TEMPLATE;
    }

    return $sResult;
  }

  public function build(fs\file $file, fs\directory $dir) {

    $doc = $file->getDocument(array(), \Sylma::MODE_EXECUTE);
    $result = null;

    $content = $this->runReflector($doc, $dir, $file);

    if ($content) {

      if ($this->readArgument('debug/show')) {

        $tmp = $this->createDocument($content);
        echo '<pre>' . $file->asToken() . '</pre>';
        echo '<pre>' . str_replace(array('<', '>'), array('&lt;', '&gt'), $tmp->asString(true)) . '</pre>';
      }

      $result = $this->getCachedFile($file);
      $template = $this->getTemplate($this->getTemplatePath());

      $sContent = $template->parseDocument($content, false);
      $result->saveText($sContent);
    }

    return $result;
  }

  protected function createReflector(dom\document $doc, fs\directory $base) {

    $result = $this->create('documented', array($this, $doc, $base));
    $result->setReflector($this->create('elemented', array($this, $result)));

    return $result;
  }

  /**
   * Build window, then return result as PHP DOM Document
   *
   * @param $reflector
   * @param string $sInstance
   * @param $file
   *
   * @return dom\handler
   */
  protected function runReflector(dom\document $doc, fs\directory $dir, fs\file $file) {

    $result = null;

    try {

      $reflector = $this->createReflector($doc, $dir);
      $sInstance = $this->getClass($doc);

      $window = $this->create('window', array($reflector, $this->getArgument(static::WINDOW_ARGS), $sInstance));
      $reflector->setWindow($window);

      $result = $reflector->asDOM();
    }
    catch (core\exception $e) {

      $e->addPath($file->asToken());
      if ($this->throwExceptions()) throw $e;
      else $e->save(false);
    }

    return $result;
  }

  public function throwExceptions($mValue = null) {

    if (!is_null($mValue)) $this->bThrow = $mValue;

    return $this->bThrow;
  }
}