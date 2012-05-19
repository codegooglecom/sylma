<?php

namespace sylma\modules\tester;
use sylma\core, sylma\dom, sylma\storage\fs, sylma\core\functions;

//require_once('modules/tester/test.php');
require_once('core/module/Domed.php');

require_once('core/argumentable.php');

abstract class Basic extends core\module\Domed implements core\argumentable {

  const NS = 'http://www.sylma.org/modules/tester';
  protected $sTitle;
  protected $aFiles = array();

  protected function getFiles() {

    return $this->aFiles;
  }

  protected function setFiles(array $aFiles) {

    $this->aFiles = $aFiles;
  }

  public function load() {

    $aResult = array();

    if (!$aFiles = $this->getFiles()) {

      $aFiles = $this->getDirectory()->getFiles(array('xml'), null, 0);
    }

    foreach ($aFiles as $file) {

      $doc = $file->getDocument();
      $doc->registerNamespaces($this->getNS());

      if (!$doc || $doc->isEmpty()) $this->throwException(sprintf('@file %s cannot be load'));

      $aTests = $this->loadDocument($doc, $file);

      $iDisabled = $aTests['disabled'];
      unset($aTests['disabled']);

      $aResult[] = array(
        'description' => $doc->readx('self:description', $this->getNS()),
        '#test' => $aTests,
        '@disabled' => $iDisabled,
      );
    }

    $this->onFinish();

    return $aResult;
  }

  protected function loadDocument(dom\handler $doc, fs\file $file) {

    $aResult = array();
    $iDisabled = 0;

    require_once('core/functions/Global.php');

    foreach ($doc->queryx('self:test') as $test) {

      if (!$test->testAttribute('disabled', false)) {
        //dspf($test->readAttribute('disabled'));
        $bResult = $this->test($test, $this->getControler(), $doc, $file);

        $aTest = array(
          '@name' => $test->getAttribute('name'),
          'result' => functions\booltostr($bResult),
        );

        if (!$bResult) $aTest['message'] = ''; // ? TODO suspicious..

        $aResult[] = $aTest;
      }
      else {

        $iDisabled++;
      }
    }

    $aResult['disabled'] = $iDisabled;

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
  protected function test(dom\element $test, $controler, dom\document $doc, fs\file $file) {

    $bResult = false;

    try {

      if (eval('$closure = function($controler) { ' . $test->read() . '; };') === null) {

        $bResult = $this->evaluate($closure, $controler);
      }
    }
    catch (core\exception $e) {

      $bResult = $this->catchException($test, $e);
    }

    return $bResult;
  }

  protected function catchException(dom\element $test, core\exception $e) {

    $bResult = false;

    $sCatch = $test->readAttribute('catch', null, false);

    if ($sCatch && $e instanceof $sCatch) {

      $bResult = true;
    }
    else {

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

      $this->throwException(t('No result node'));
    }

    return $result;
  }

  public function compareNodes(dom\node $node1, dom\node $node2) {

    $el = $this->loadDomElement($node1);

    if ($el->compare($this->loadDomElement($node2)) !== $el::COMPARE_SUCCESS) {

      $this->throwException(sprintf('DOMs not equals with : %s', $el->compareBadNode->asToken()));
    }

    return true;
  }

  public function asArgument() {

    $result = $this->createArgument(array(
      'group' => array(
        'description' => $this->sTitle,
        '#group' => $this->load(),
      ),
    ), self::NS);

    return $result;
  }

  protected function onFinish() {


  }
}