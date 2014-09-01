<?php

namespace sylma\modules\stepper;
use sylma\core, sylma\dom, sylma\storage\fs;

class Browser extends core\module\Domed {

  const FACTORY_RELOAD = false;
  const FILE_MANAGER = 'fs/editable';

  const NS = 'http://2013.sylma.org/modules/stepper';

  public function __construct(core\argument $args, core\argument $post) {

    //$this->setDirectory(__DIR__);
    $this->setNamespace(self::NS);
    $this->loadDefaultSettings();

    $this->setSettings($post);
    $this->setSettings($args);

    if ($sDirectory = $this->read('dir', false)) {

      $this->setDirectory($this->getManager(self::FILE_MANAGER)->getDirectory($sDirectory));
    }
  }

  public function getDirectory($sPath = '', $bDebug = false) {

    return parent::getDirectory($sPath, $bDebug);
  }

  public function getCollection($bDebug = false) {

    return $this->read('file', $bDebug);
  }

  public function getItems() {

    if ($this->getCollection(false)) {

      $aResult = $this->getDirectories();
    }
    else {

      $aResult = $this->getTests();
    }

    return $aResult;
  }

  public function getTests() {

    $aResult = array();

    $args = $this->createArgument(array(
      'extensions' => array('tml'),
    ));

    $sCurrentDirectory = (string) $this->getDirectory();
    $iBase = strlen($sCurrentDirectory);

    foreach ($this->getDirectory('', true)->browse($args, false) as $file) {

      $sDirectory = '';
      $sSubDirectory = (string) $file->getParent();

      if ($sCurrentDirectory !== $sSubDirectory) {

        $sDirectory = substr($sSubDirectory, $iBase);
      }

      $aResult['test'][] = array(
        'file' => substr($file, $iBase + 1),
        'directory' => $sDirectory,
      );
    }

    return $aResult;
  }

  public function getDirectories() {

    $aResult = array();
    $file = $this->getManager(self::FILE_MANAGER)->getFile($this->getCollection());
    $collection = $this->createOptions($file->asDocument());

    $this->setDirectory($file->getParent());

    foreach ($collection as $dir) {

      $aResult[] = $this->buildChild($dir);
    }

    return array('_all' => $aResult);
  }

  protected function buildChild(core\argument $child) {

    switch ($child->getName()) {

      case 'directory' :

        $aResult = array(
          'path' => (string) $this->getDirectory($child->read('@path'), true),
          '_alias' => 'directory',
        );
        break;

      case 'group' :

        $aResult = array(
          'name' => $child->read('@name'),
          '_alias' => 'group',
          '_all' => array(),
        );

        foreach ($child as $sub) {

          $aResult['_all'][] = $this->buildChild($sub);
        }

        break;

      default :

        $this->launchException('Bad collection child');
    }

    return $aResult;
  }

  public function loadTest() {

    $aResult = $this->buildTest($this->createOptions($this->read('path')));

    return $aResult;
  }

  public function loadDirectory() {

    return $this->getTests();
  }

  protected function buildTest(core\argument $test) {

    $aResult = array();

    foreach ($test as $page) {

      $aPage = array(
        'url' => $page->read('@url', false),
        //'element' => $page->read('element'),
      );

      $aSteps = array();

      foreach ($page->get('steps') as $step) {

        if ($step->isEmpty()) continue;

        $aSteps[] = $this->buildStep($step);
      }

      $aPage['steps'][] = array('_all' => $aSteps);
      $aResult['page'][] = $aPage;

      $aResult['width'] = $test->read('@width', false);
      $aResult['height'] = $test->read('@height', false);
    }

    return array_filter($aResult);
  }

  protected function buildStep(core\argument $step) {

    $aResult = array(
      '_alias' => $step->getName(),
    );

    switch ($step->getName()) {

      case 'event' :

        $aResult['name'] = $step->read('@name');
        $aResult['element'] = $step->read('@element');
        break;

      case 'input' :

        $aResult['element'] = $step->read('@element');
        $aResult['value'] = $step->read();
        break;

      case 'watcher' :

        $aResult['element'] = $step->read('@element');
        $aResult['delay'] = $step->read('@delay', false);


        foreach ($step->query('property', false) as $property) {

          $aResult['property'][] = array(
            'name' => $property->read('@name'),
            'value' => $property->read(),
          );
        }

        $this->loadVariable($step, $aResult);

        break;

      case 'snapshot' :

        $aResult['element'] = $step->read('@element');
        $aResult['content'] = $step->read('content', false);

        foreach ($step->query('exclude', false) as $exclude) {

          $aResult['excludes'][] = array(
            'element' => $exclude->read('@element'),
          );
        }
        break;

      case 'call' :

        $aResult['path'] = $step->read('@path');
        $aResult['get'] = $step->read('@method', false) === 'get';

        $this->loadVariable($step, $aResult);
        break;

      case 'query' :

        $aResult['value'] = $step->read();
        $aResult['creation'] = $step->read('@creation');
        $aResult['timeshift'] = $step->read('@timeshift', false);
        $aResult['connection'] = $step->read('@connection', false);
        break;
    }

    return $aResult;
  }

  protected function loadVariable(core\argument $step, array &$aStep) {

    if ($var = $step->get('variable', false)) {

      $aStep['variable'][] = array(
        'name' => $var->read('@name'),
      );
    }
  }

  public function saveTest() {

    $aTest = json_decode($this->read('test'), true);
    $doc = $this->createArgument($aTest)->asDOM();
    $file = $this->getManager(self::FILE_MANAGER)->getFile($this->read('file'), $this->getDirectory(), fs\file::DEBUG_EXIST);

    $doc->saveFile($file, true);

    $this->getManager(self::PARSER_MANAGER)->getContext('messages')->add(array('content' => "File <strong>$file</strong> saved"));

    return true;
  }

  public function getCaptcha() {

    $captcha = new \sylma\modules\captcha\Type('');

    return $captcha->getKey();
  }

  public function runQuery() {

    $file = $this->getFile($this->read('file'));
    $creation = new \DateTime($this->read('creation'));
    $sConnection = $this->read('connection');

    $diff = $creation->diff(new \DateTime());

    $sContent = preg_replace_callback('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', function($aMatch) use($diff) {

      try
      {
        $date = new \DateTime($aMatch[0]);

      } catch (\Exception $e) {

        \Sylma::throwException($e->getMessage());
      }

      $new = $date->add($diff);

      return $new->format('Y-m-d H:i:s');

    }, $file->execute());

    $this->getManager(self::DB_MANAGER)->getConnection($sConnection)->execute($sContent);

    return true;
  }
}

