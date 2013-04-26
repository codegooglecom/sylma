<?php

namespace sylma\core\window;
use sylma\core;

interface scripted extends core\stringable {

  public function setScript(core\request $path, core\argument $post, $sContext);
}
