<?php

namespace sylma\parser\action\test;
use \sylma\modules\tester, \sylma\core, \sylma\dom, \sylma\storage\fs, \sylma\parser;

require_once('modules/tester/Basic.php');

class Basic extends tester\Basic {

  const NS = 'http://www.sylma.org/parser/action/test';
  const FS_CONTROLER = 'fs/editable';

  protected $sTitle = 'Action';

  public function __construct(parser\action\Controler $controler = null) {

    \Sylma::getControler('dom');

    require_once(dirname(dirname(__dir__)) . '/action.php');

    $this->setDirectory(__file__);
    $this->setNamespaces(array(
        'self' => self::NS,
        'le' => parser\action::NS,
    ));

    if (!$controler) $controler = \Sylma::getControler('action');

    $this->setControler($controler);
    //$this->setFiles(array($this->getFile('basic.xml')));
  }

  public function getResult($sType) {

    $action = $this->getArgument('action');

    switch ($sType) {

      case 'dom' : $result = $action->asDOM(); break;
      case 'txt' : $result = $action->asString(); break;
      case 'obj' : $result = $action->asObject(); break;
      default :

        $this->throwException(txt('Unknown action type : %s', $sType));
    }

    return $result;
  }
  
  protected function test(dom\element $test, $controler, dom\document $doc, fs\file $file) {

    $result = null;
    $node = $test->getx('le:action');

    $fs = $this->getControler('fs');

    $dir = $this->createTempDirectory();

    try {

      $action = $controler->buildAction($this->createDocument($node), array(), $dir, $file->getParent());
      $this->setArgument('action', $action);

      $result = parent::test($test->getx('self:expected'), $this, $doc, $file);
    }
    catch (core\exception $e) {

      $e->save();
    }

    //$dir->delete();

    return $result;
  }
}

