<?php

namespace sylma\modules\stepper;
use sylma\core, sylma\dom;

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

    foreach ($this->getDirectory()->getFiles(array('tml')) as $file) {

      $aResult['test'][] = array(
        'file' => $file->getName(),
      );
    }

    return $aResult;
  }

  public function load() {

    $file = $this->getDirectory()->getFile($this->read('file'));
//    $aResult = array();

//    if ($file = $this->getFile($sFile, false)) {

      $test = $this->createOptions($file->getDocument(), false);
      $aResult = $this->buildTest($test);
//    }

    return $aResult;
  }

  protected function buildTest(core\argument $test) {

    $aResult = array();

    foreach ($test as $page) {

      $aPage = array(
        'url' => $page->read('@url'),
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

            break;

          case 'snapshot' :

            $aStep['element'] = $step->read('@element');
            $aStep['content'] = $step->read('content', false);
            break;

          case 'captcha' :

            $aStep['element'] = $step->read('@element');
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

    return $aResult;
  }

  public function saveTest() {

    $aTest = json_decode($this->read('test'), true);
    $doc = $this->createArgument($aTest)->asDOM();
    $file = $this->getDirectory()->createFile($this->read('file'));

    $doc->saveFile($file, true);

    $this->getManager(self::PARSER_MANAGER)->getContext('messages')->add(array('content' => 'File saved'));

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

    }, $file->read());

    $this->getManager(self::DB_MANAGER)->getConnection()->execute($sContent);

    return true;
  }
}

