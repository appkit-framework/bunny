<?php

declare(strict_types=1);

use Bunny\Client;
use React\EventLoop\Loop;
use function React\Async\async;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$client = new Client();
Loop::futureTick(async(static function () use ($argv, $client): void {
    $channel = $client->channel();

    $channel->exchangeDeclare('logs', 'fanout');

    $data = implode(' ', array_slice($argv, 1));
    $channel->publish($data, [], 'logs');
    echo ' [x] Sent "' . $data . '"' . PHP_EOL;

    $channel->close();
    $client->disconnect();
}));
