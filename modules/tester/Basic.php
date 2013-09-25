<?php

namespace sylma\modules\tester;
use sylma\core, sylma\dom, sylma\storage\fs, sylma\core\functions;

abstract class Basic extends Asserter {

  protected static $sArgumentClass = 'sylma\core\argument\Filed';
  protected static $sFactoryClass = '\sylma\core\factory\Reflector';

  const NS = 'http://www.sylma.org/modules/tester';
  const PREFIX = 'self';

  protected $sTitle;
  protected $aFiles = array();

  const FILE_MANAGER = 'fs/editable';

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

    return array_filter($aResult);
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

    if (!$doc->isEmpty()) {

      if ($doc->getRoot()->getNamespace() === static::NS) {

        //dsp($doc->getRoot()->getNamespace());
        $aTests = $this->loadDocument($doc, $file);

        $iDisabled = $aTests['disabled'];
        unset($aTests['disabled']);

        $aResult = array(
          'description' => $doc->readx('self:description', $this->getNS()),
          '#test' => $aTests,
          '@disabled' => $iDisabled,
        );
      }
      else {

        $aResult = array();
      }
    }

    return $aResult;
  }

  protected function loadDocument(dom\handler $doc, fs\file $file) {

    $aResult = array();
    $iDisabled = 0;

    require_once('core/functions/Global.php');

    $tests = $doc->queryx('self:test[@standalone]', array(), false);

    if (!$tests->length) {

      $tests = $doc->queryx('self:test');
    }

    foreach ($tests as $test) {

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

    if (!is_callable($closure)) {

      $this->throwException('Cannot call test');
    }

    //core\exception\Basic::throwError(false);
    $mResult = $closure($controler);
    //core\exception\Basic::throwError(true);

    return $mResult;
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

    $this->resetCount();
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
      $e->addPath('Test ID : ' . $test->readAttribute('name'));

      if ($sCatch) $e->addPath(sprintf('Exception of type %s expected', $sCatch));
      //$e->addPath($test->asString());

      $e->save(false);
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

  public function compareNodes($node1, $node2) {

    if ($node1 instanceof dom\collection) {

      if (!$node2 instanceof dom\collection) {

        $this->launchException('Nodes are not equal types');
      }

      $root1 = $this->createDocument('root')->getRoot();
      $root2 = $this->createDocument('root')->getRoot();

      $root1->set($node1);
      $root2->set($node2);

      $node1 = $root1;
      $node2 = $root2;
    }

    $el = $this->loadDomElement($node1);
    $iResult = $el->compare($this->loadDomElement($node2));

    if ($iResult !== $el::COMPARE_SUCCESS) {

      $node = $el->compareBadNode;
      $sNode = $node instanceof core\tokenable ? $node->asToken() : '[undefined]';

      //$this->throwException(sprintf('Node %s not equals with node %s', $el->asToken(), $node2->asToken()));
      $this->launchException('Nodes not equals in ' . $sNode . $this->findDiff($node1->asString(), $node2->asString()), get_defined_vars());
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