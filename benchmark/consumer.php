<?php

declare(strict_types=1);

use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;
use React\EventLoop\Loop;
use function React\Async\async;

require_once __DIR__ . '/../vendor/autoload.php';

$client = new Client();
Loop::futureTick(async(static function () use ($client): void {
    $channel = $client->channel();

    $channel->queueDeclare('bench_queue');
    $channel->exchangeDeclare('bench_exchange');
    $channel->queueBind('bench_exchange', 'bench_queue');

    $time = null;
    $count = 0;

    $channel->consume(static function (Message $msg, Channel $channel, Client $c) use (&$time, &$count): void {
        if ($time === null) {
            $time = microtime(true);
        }

        if ($msg->content === 'quit') {
            $runTime = microtime(true) - $time;
            printf("Consume: Pid: %s, Count: %s, Time: %.6f, Msg/sec: %.0f\n", getmypid(), $count, $runTime, 1 / $runTime * $count);
            $c->disconnect();
        } else {
            ++$count;
        }
    }, 'bench_queue', '', false, true);
}));
