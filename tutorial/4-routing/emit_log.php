<?php

declare(strict_types=1);

use Bunny\Client;
use React\EventLoop\Loop;
use function React\Async\async;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$client = new Client();
Loop::futureTick(async(static function () use ($argv, $client): void {
    $channel = $client->channel();

    $channel->exchangeDeclare('direct_logs', 'direct');

    $severity = isset($argv[1]) && !empty($argv[1]) ? $argv[1] : 'info';
    $data = implode(' ', array_slice($argv, 2));
    if (empty($data)) {
        $data = 'Hello World!';
    }

    $channel->publish($data, [], 'direct_logs', $severity);
    echo ' [x] Sent ' . $severity . ':' . $data . PHP_EOL;

    $channel->close();
    $client->disconnect();
}));
