<?php
/*
 * Description
 * Created on 17 oct. 2008
 */

require('protected/sylma/Init.php'); // set SYLMA path
define('MAIN_DIRECTORY', 'protected'); // set site path

echo Sylma::init('server.yml'); // relative path to yaml server's config file