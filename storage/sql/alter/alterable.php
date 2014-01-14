<?php

namespace sylma\storage\sql\alter;

interface alterable {

  function asCreate();
  function asUpdate();
}
