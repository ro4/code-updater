<?php
require __DIR__ . '/bootstrap/app.php';
use GitWrapper\GitWrapper;

$config   = require_once('config.php');
$wrapper  = new GitWrapper();
$port     = isset($config['port']) ? $config['port'] : 4000;
$projects = isset($config['projects']) ? $config['projects'] : [];
$password = isset($config['password']) ? $config['password'] : [];
$http     = new swoole_http_server("0.0.0.0", $port);

$http->on("start", function ($server) use ($port) {
    echo "code updater is running at port:{$port}\n";
});

$http->on("request", function ($request, $response) use ($wrapper, $projects, $password) {
    $response->header("Content-Type", "application/json");
    $method = $request->server['request_method'];
    $path   = $request->server['path_info'];
    if ($method == 'POST' && $path == '/push') {
        $project       = isset($request->post['project']) ? $request->post['project'] : null;
        $passwordKey   = isset($password['key']) ? $password['key'] : null;
        $passwordValue = isset($password['value']) ? $password['value'] : null;
        $password      = isset($request->post[$passwordKey]) ? $request->post[$passwordKey] : null;
        $path          = isset($projects[$project]['path']) ? $projects[$project]['path'] : null;
        $pass          = ($password && $password == $passwordValue);
        if ($pass && $path) {
            echo date('[Y-m-d H:i:s]:') . $project . ' ' . $wrapper->workingCopy($path)->pull();
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