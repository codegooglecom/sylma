<?php

namespace sylma\dom;

interface collection {

  function addCollection(collection $collection);
  function getLast();
}