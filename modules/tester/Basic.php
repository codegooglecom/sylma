<?php

namespace sylma\modules\tester;
use sylma\core, sylma\dom, sylma\storage\fs, sylma\core\functions;

abstract class Basic extends core\module\Domed implements core\argumentable {

  const NS = 'http://www.sylma.org/modules/tester';
  protected $sTitle;
  protected $aFiles = array();

  protected function getFiles() {

    if (!$this->aFiles) {

      $this->aFiles = $this->getDirectory()->getFiles(array('xml'), null, 1);
    }

    return $this->aFiles;
  }

  protected function setFiles(array $aFiles) {

    $this->aFiles = $aFiles;
  }

  public function loadAll() {

    $aResult = array();

    foreach ($this->getFiles() as $file) {

      $aResult[] = $this->loadFile($file);
    }

    $this->onFinish();

    return $aResult;
  }
/*
  public function loadNext() {

    if ($params = $this->getSession()) {

    }
    else {

      $aFiles = $this->getFiles();

      $param = $this->createArgument(array(
        'file' => array_shift($aFiles),
      ))

      $this->setSession($params);
    }
  }
*/
  protected function loadFile(fs\file $file) {

    $doc = $file->getDocument();
    $doc->registerNamespaces($this->getNS());

    if ($doc->isEmpty()) $this->throwException(sprintf('@file %s cannot be load'));

    $aTests = $this->loadDocument($doc, $file);

    $iDisabled = $aTests['disabled'];
    unset($aTests['disabled']);

    return array(
      'description' => $doc->readx('self:description', $this->getNS()),
      '#test' => $aTests,
      '@disabled' => $iDisabled,
    );
  }

  protected function loadDocument(dom\handler $doc, fs\file $file) {

    $aResult = array();
    $iDisabled = 0;

    require_once('core/functions/Global.php');

    foreach ($doc->queryx('self:test') as $test) {

      if (!$test->testAttribute('disabled', false)) {

        $aResult[] = $this->loadElement($test, $doc, $file);
      }
      else {

        $iDisabled++;
      }
    }

    $aResult['disabled'] = $iDisabled;

    return $aResult;
  }

  protected function loadElement(dom\element $el, dom\handler $doc, fs\file $file) {

    $bResult = $this->test($el, $el->read(), $this->getControler(), $doc, $file);

    $aResult = array(
      '@name' => $el->getAttribute('name'),
      'result' => functions\booltostr($bResult),
    );

    if (!$bResult) $aResult['message'] = ''; // ? TODO suspicious..

    return $aResult;
  }

  protected function evaluate($closure, $controler) {

    return $closure($controler);
  }

  /**
   *
   * @param dom\element $test
   * @param type $controler
   * @param dom\document $doc
   * @param fs\file $file
   * @return boolean
   */
  protected function test(dom\element $test, $sContent, $controler, dom\document $doc, fs\file $file) {

    $bResult = false;

    try {

      if (eval('$closure = function($controler) { ' . $sContent . '; };') === null) {

        $bResult = $this->evaluate($closure, $controler);
      }
    }
    catch (core\exception $e) {

      $bResult = $this->catchException($test, $e, $file);
    }

    return $bResult;
  }

  protected function catchException(dom\element $test, core\exception $e, fs\file $file) {

    $bResult = false;

    $sCatch = $test->readAttribute('catch', null, false);

    if ($sCatch && $e instanceof $sCatch) {

      $bResult = true;
    }
    else {

      $e->addPath($file->asToken());
      $e->addPath($test->readAttribute('name'));
      //$e->addPath($test->asString());
      $e->save();
    }

    return $bResult;
  }

  public function setControler($controler, $sName = '') {

    if ($sName) parent::setControler($controler, $sName);
    else $this->controler = $controler;
  }

  public function getNamespace($sPrefix = null) {

    return parent::getNamespace($sPrefix);
  }

  public function loadDomElement(dom\node $node) {

    if ($node instanceof dom\document) $result = $node->getRoot();
    else $result = $node;

    if (!$result) {

      $this->throwException('No result node');
    }

    return $result;
  }

  public function compareNodes(dom\node $node1, dom\node $node2) {

    $el = $this->loadDomElement($node1);

    if ($el->compare($this->loadDomElement($node2)) !== $el::COMPARE_SUCCESS) {

      //$this->throwException(sprintf('Node %s not equals with node %s', $el->asToken(), $node2->asToken()));
      $this->throwException(sprintf('Node %s not equals with node %s', $this->show($el, false), $this->show($node2, false)));
    }

    return true;
  }

  public function asArgument() {

    $result = $this->createArgument(array(
      'group' => array(
        'description' => $this->sTitle,
        '#group' => $this->loadAll(),
      ),
    ), self::NS);

    return $result;
  }

  protected function onFinish() {


  }
}