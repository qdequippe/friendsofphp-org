<?php

/**
 * This router is required if you are running the built in PHP web server
 * as it attempts to route static files through the natural router(index).
 *
 * @see https://github.com/symfony/symfony/issues/26099#issuecomment-597135405
 */
if (php_sapi_name() == 'cli-server') {
    $info = parse_url($_SERVER['REQUEST_URI']);

    if ($info !== false && file_exists(sprintf("./%s", $info['path']))) {
        return false;
    } else {
        include_once "index.php";
        return true;
    }
}
