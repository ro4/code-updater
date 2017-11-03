<?php
require __DIR__ . '/bootstrap/app.php';
use GitWrapper\GitWrapper;
use App\Helpers\Config;

$wrapper = new GitWrapper();
$port    = Config::instance()->port ?: 4000;
$http    = new swoole_http_server("0.0.0.0", $port);

$http->on("start", function ($server) use ($port) {
    echo "code updater is running at port:{$port}\n";
});

$http->on("request", function ($request, $response) use ($wrapper) {
    $response->header("Content-Type", "application/json");
    $method = $request->server['request_method'];
    $path   = $request->server['path_info'];
    if ($method == 'POST' && $path == '/push') {
        $project       = access_array($request->post, 'project');
        $passwordKey   = access_object(Config::instance()->password, 'key');
        $passwordValue = access_object(Config::instance()->password, 'value');;
        $password = access_array($request->post, $passwordKey);
        $path     = access_object(Config::instance()->projects, "{$project}.path");
        $pass     = ($password && $password == $passwordValue);
        if ($pass && $path) {
            $git = $wrapper->workingCopy($path);
            $msg =  date('[Y-m-d H:i:s]:') . $project . ' ' . $git->pull();
            $msg .= "\n---------------latest 3 commits--------------\n";
            $msg .= $git->log(['-3']);
            echo $msg;
            notify($msg);
            $response->status(200);
            $response->end(json_encode(['msg' => 'ok']));
        } else {
            $response->status(500);
            $response->end(json_encode(['msg' => 'failed']));
        }
    } else {
        $response->status(404);
        $response->end(json_encode(['msg' => 'not found']));
    }
});

$http->start();