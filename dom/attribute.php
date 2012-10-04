<?php

namespace sylma\dom;

interface attribute extends node, namespaced {

  function getValue();
  function setValue($sValue);
}