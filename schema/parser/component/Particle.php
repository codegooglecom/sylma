<?php

namespace sylma\schema\parser\component;
use sylma\core, sylma\schema\parser, sylma\parser\reflector;

abstract class Particle extends Basic implements parser\particle {

  abstract public function getElement($sName);
}

