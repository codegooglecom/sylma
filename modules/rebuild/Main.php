<?php

namespace sylma\modules\rebuild;
use sylma\core, sylma\dom;

class Main extends core\module\Domed implements dom\domable {

  const PARSER_ACTION = 'action';

  public function __construct() {

    $this->setDirectory(__file__);

    $this->setSettings(\Sylma::get('modules/rebuild'));
  }

  /**
   *
   * @param type $sPath
   * @return string
   */
  public function load($sPath) {

    $file = $this->getFile($sPath);
    $parent = $this->getControler('parser')->getContext('action/current');

    dsp("Rebuild : $sPath");

    if (!in_array((string) $file, $this->get('exclude/run')->query())) {

      if ($file->getExtension() == 'eml') {

        $action = $this->getManager(self::PARSER_ACTION)->getAction((string) $file->asPath());
        $action->getContext('default');
      }
      else {

        $this->getManager(self::PARSER_MANAGER)->load($file, array(
          'contexts' => $parent->getContexts(),
        ), true, true);
      }
    }
//$parent->getContexts()->get('message');
    return '1';
  }

  public function asDOM() {

    $this->loadDefaultSettings();

    $common = $this->createArgument(array(
      'extensions' => array('eml','vml'),
      'depth' => 99,
      'only-path' => false,
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

