<?php

namespace sylma\modules\rebuild;
use sylma\core, sylma\dom;

class Main extends core\module\Domed implements dom\domable {

  const PARSER_ACTION = 'action';

  public function __construct() {

    $this->setDirectory(__file__);
  }

  /**
   *
   * @param type $sPath
   * @return string
   */
  public function load($sPath) {

    $file = $this->getFile($sPath);
    $parent = $this->getControler('parser')->getContext('action/current');

    if ($file->getExtension() == 'eml') {

      $action = $this->getManager(self::PARSER_ACTION)->getAction((string) $file);
      $action->getContexts();
    }
    else {

      $this->getManager(self::PARSER_MANAGER)->load($file, array(
        'contexts' => $parent->getContexts(),
      ), true, true);
    }
//$parent->getContexts()->get('message');
    return '1';
  }

  public function asDOM() {

    $files = $this->getDirectory('/')->browse($this->createArgument(array(
      'extensions' => array('eml','vml'),
      'excluded' => array('/sylma', '/users'),
      'depth' => 99,
      'only-path' => false,
    )));

    $this->loadDefaultArguments();

    return $this->getTemplate('source.xsl')->parseDocument($files->asDOM());
  }
}

