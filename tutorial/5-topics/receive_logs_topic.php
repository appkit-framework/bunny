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

    $channel->exchangeDeclare('topic_logs', 'topic');
    $queue = $channel->queueDeclare('', false, false, true, false);

    $bindingKeys = array_slice($argv, 1);
    if (empty($bindingKeys)) {
        file_put_contents('php://stderr', sprintf("Usage: %s [binding_key]\n", $argv[0]));
        $client->disconnect();
        exit(1);
    }

    foreach ($bindingKeys as $bindingKey) {
        $channel->queueBind('topic_logs', $queue->queue, $bindingKey);
    }

    echo ' [*] Waiting for logs. To exit press CTRL+C', "\n";

    $channel->consume(
        static function (Message $message, Channel $channel, Client $client): void {
            echo ' [x] ' . $message->routingKey . ':' . $message->content . PHP_EOL;
        },
        $queue->queue,
        '',
        false,
        true,
    );
}));
