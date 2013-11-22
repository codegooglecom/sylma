<?php

namespace sylma\modules\stepper;
use sylma\core;

class Browser extends core\module\Domed {

  const FACTORY_RELOAD = false;
  const FILE_MANAGER = 'fs/editable';

  const NS = 'http://2013.sylma.org/modules/stepper';

  public function __construct(core\argument $args, core\argument $post) {

    $this->setDirectory(__DIR__);
    $this->setNamespace(self::NS);
    $this->loadDefaultSettings();

    $this->setSettings($post);
    $this->setSettings($args);

    $this->setFile($this->getFile($this->read('file')));
  }

  public function getFile($sPath = '', $bDebug = true) {

    return parent::getFile($sPath, $bDebug);
  }

  public function getTests() {

    //foreach ($this->query('test') as $test) {

      $aTest = array();
      $test = $this->createOptions($this->read('file'), false);

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

            case 'watcher' :

              $aStep['element'] = $step->read('@element');
              break;

            case 'snapshot' :

              $aStep['element'] = $step->read('@element');
              $aStep['content'] = $step->read('content', false);
              break;
          }

          $aSteps[] = $aStep;
        }

        $aPage['steps'][] = array('_all' => $aSteps);
        $aTest['page'][] = $aPage;
      }

      $aResult['test'][] = $aTest;
    //}

    return $aResult;
  }

  public function saveTest() {

    $aTest = json_decode($this->read('test'), true);
    $doc = $this->createArgument($aTest)->asDOM();
    $file = $this->getFile($this->read('file'));

    $doc->saveFile($file, true);

    $this->getManager(self::PARSER_MANAGER)->getContext('messages')->add(array('content' => 'File saved'));
  }
}

