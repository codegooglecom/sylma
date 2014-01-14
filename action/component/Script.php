<?php

namespace sylma\action\component;
use sylma\core, sylma\dom, sylma\parser\languages\common;

class Script extends Caller implements common\arrayable {

  public function asArray() {

    $path = $this->loadPath($this->readx('@path'));

    $parser = $this->getWindow()->addControler(self::PARSER_MANAGER);
    $fs = $this->getWindow()->addControler(self::FILE_MANAGER);

    if ($sMode = $this->readx('@mode')) {

      if (!in_array($sMode, array('get', 'post', 'context'))) {

        $this->launchException('Mode unknown for script call');
      }
    }
    else {

      $sMode = 'get';
    }

    if ($sMode === 'get') $sMode = 'arguments';

    $args = $this->createObject('argument', array(array_merge($path->getArguments()->query(), $this->loadArguments())), null, false);

    $result = $parser->call('load', array($fs->call('getFile', array((string) $path->asFile(true))), array(
      $sMode => $args,
      'contexts' => $this->getWindow()->getVariable('contexts'),
    )));

    return array($result);
  }
}

