<?php

declare(strict_types=1);

use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;
use React\EventLoop\Loop;
use function React\Async\async;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$client = new Client();
Loop::futureTick(async(static function () use ($client): void {
    $channel = $client->channel();

    $channel->queueDeclare('task_queue', false, true, false, false);

    echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

    $channel->qos(0, 1);
    $channel->consume(
        async(static function (Message $message, Channel $channel, Client $client): void {
            echo ' [x] Received ' . $message->content . PHP_EOL;
            sleep(substr_count($message->content, '.'));
            echo ' [x] Done' . PHP_EOL;
            $channel->ack($message);
        }),
        'task_queue',
    );
}));
