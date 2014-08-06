<?php

namespace sylma\modules\rebuild;
use sylma\core, sylma\dom;

class Main extends core\module\Domed implements dom\domable {

  const PARSER_ACTION = 'action';

  public function __construct($args, $post, $contexts) {

    $this->setDirectory(__file__);

    $this->setSettings(\Sylma::get('modules/rebuild'));
    $this->aContexts = array(
      'contexts' => $contexts,
      'post' => $post,
      'arguments' => $args,
    );
  }

  /**
   *
   * @param type $sPath
   * @return string
   */
  public function load($sPath, $bRun = true) {

    $this->aContexts['arguments']->set('path');

    $file = $this->getFile($sPath);
    //$parent = $this->getControler('parser')->getContext('action/current');

    dsp("Rebuild : $sPath");

    if (!in_array((string) $file, $this->get('exclude/run')->query())) {

      if ($file->getExtension() == 'eml') {

        $action = $this->getManager(self::PARSER_ACTION)->getAction((string) $file->asPath());
        $action->getContext('default');
      }
      else {

        $manager = $this->getManager(self::PARSER_MANAGER);

        $manager->load($file, $this->aContexts, true, is_null($bRun) ? true : $bRun);
      }
    }
//$parent->getContexts()->get('message');
    return '1';
  }

  public function asDOM() {

    $this->loadDefaultSettings();

    $common = $this->createArgument(array(
      'extensions' => array('eml','vml'),
      'mode' => 'argument',
    ));

    $root = $common;
    $root->set('excluded', array('/sylma', '/users'));

    $files = $this->getDirectory('/')->browse($root);
    $doc = $files->asDOM();

    $modules = $common;
    $modules->set('excluded', array('/sylma/modules/tester', 'test'));
    $doc->add($this->getDirectory('/sylma')->browse($modules)->query());

    return $this->getTemplate('source.xsl')->parseDocument($doc);
  }
}

