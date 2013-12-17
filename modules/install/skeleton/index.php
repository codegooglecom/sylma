<?php
/*
 * Description
 * Created on 17 oct. 2008
 */

namespace sylma;

const ROOT = 'protected'; // set site path
require(ROOT . '/sylma/Init.php'); // set SYLMA path

\Sylma::init('server.yml');
echo \Sylma::render();
