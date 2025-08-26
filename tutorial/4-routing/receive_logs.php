<?php

declare(strict_types=1);

use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;
use React\EventLoop\Loop;
use function React\Async\async;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$client = new Client();
Loop::futureTick(async(static function () use ($argv, $client): void {
    $channel = $client->channel();

    $channel->exchangeDeclare('direct_logs', 'direct');
    $queue = $channel->queueDeclare('', false, false, true, false);

    $severities = array_slice($argv, 1);
    if (empty($severities)) {
        file_put_contents('php://stderr', sprintf("Usage: %s [info] [warning] [error]\n", $argv[0]));
        $client->disconnect();
        exit(1);
    }

    foreach ($severities as $severity) {
        $channel->queueBind('direct_logs', $queue->queue, $severity);
    }

    echo ' [*] Waiting for logs. To exit press CTRL+C' . PHP_EOL;

    $channel->consume(
        static function (Message $message, Channel $channel, Client $client): void {
            echo ' [x] ', $message->routingKey, ':', $message->content, "\n";
        },
        $queue->queue,
        '',
        false,
        true,
    );
}));
