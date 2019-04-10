<?php

include __DIR__ . '/../common.inc.php';

require __DIR__ . '/RestServer/RestServer.php';

require __DIR__ . '/FourBlitController.php';

$server = new \RestServer\RestServer('debug');
$server->addClass('FourBlitController');
$server->handle();
