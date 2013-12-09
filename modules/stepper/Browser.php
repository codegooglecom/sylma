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

  public function getDirectory($sPath = '', $bDebug = true) {

    return parent::getDirectory($sPath, $bDebug);
  }

  public function getTests() {

    $aResult = array();

    $args = $this->createArgument(array(
      'extensions' => array('tml'),
    ));

    $iBase = strlen((string) $this->getDirectory()) + 1;

    foreach ($this->getDirectory()->browse($args, false) as $file) {

      $aResult['test'][] = array(
        'file' => substr($file, $iBase),
      );
    }

    return $aResult;
  }

  public function load() {

    $file = $this->getDirectory()->getFile($this->read('file'));

    $test = $this->createOptions($file->asDocument(array(), \Sylma::MODE_EXECUTE), false);
    $aResult = $this->buildTest($test);

    return $aResult;
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

        $aStep = array(
          '_alias' => $step->getName(),
        );

        switch ($step->getName()) {

          case 'event' :

            $aStep['name'] = $step->read('@name');
            $aStep['element'] = $step->read('@element');
            break;

          case 'input' :

            $aStep['element'] = $step->read('@element');
            $aStep['value'] = $step->read();
            break;

          case 'watcher' :

            $aStep['element'] = $step->read('@element');

            foreach ($step->query('property', false) as $property) {

              $aStep['property'][] = array(
                'name' => $property->read('@name'),
                'value' => $property->read(),
              );
            }

            $this->loadVariable($step, $aStep);

            break;

          case 'snapshot' :

            $aStep['element'] = $step->read('@element');
            $aStep['content'] = $step->read('content', false);
            break;

          case 'call' :

            $aStep['path'] = $step->read('@path');
            $this->loadVariable($step, $aStep);
            break;

          case 'query' :

            $aStep['value'] = $step->read();
            $aStep['creation'] = $step->read('@creation');
            $aStep['timeshift'] = $step->read('@timeshift', false);
            break;
        }

        $aSteps[] = $aStep;
      }

      $aPage['steps'][] = array('_all' => $aSteps);
      $aResult['page'][] = $aPage;
    }

    return array_filter($aResult);
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
    $diff = $creation->diff(new \DateTime());

    $sContent = preg_replace_callback('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', function($aMatch) use($diff) {

      //dsp($sMatch);
      $date = new \DateTime($aMatch[0]);
      $new = $date->add($diff);

      return $new->format('Y-m-d H:i:s');

    }, $file->execute());
//$this->getDirectory()->createFile('temp')->saveText($sContent);
    $this->getManager(self::DB_MANAGER)->getConnection()->execute($sContent);

    return true;
  }
}

