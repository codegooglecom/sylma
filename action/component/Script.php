<?php

namespace sylma\action\component;
use sylma\core, sylma\dom, sylma\parser\languages\common;

class Script extends Caller implements common\arrayable {

  public function build() {

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

    $args = $post = null;

    if ($sMode === 'get') {

      $args = $this->createObject('argument', array(array_merge($path->getArguments()->query(), $this->loadArguments())), null, false);
    }
    else {

      $args = $this->createObject('argument', array($path->getArguments()->query()), null, false);
      $post = $this->createObject('argument', array($this->loadArguments()), null, false);
    }

    $result = $parser->call('load', array($fs->call('getFile', array((string) $path->asFile(true))), array_filter(array(
      'arguments' => $args,
      'post' => $post,
      'contexts' => $this->getWindow()->getVariable('contexts'),
    ))));

    if ($this->readx('@hollow')) {

      $result = $this->getWindow()->createInstruction($result);
    }

    return array($result);
  }

  public function asArray() {

    return $this->build();
  }
}

