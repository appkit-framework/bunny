<?php

declare(strict_types=1);

namespace Bunny;

use function getmypid;
use function microtime;
use function printf;

require_once __DIR__ . '/../vendor/autoload.php';

$c = new Client();
$ch = $c->connect()->channel();

$ch->queueDeclare('bench_queue');
$ch->exchangeDeclare('bench_exchange');
$ch->queueBind('bench_exchange', 'bench_queue');

$time = null;
$count = 0;

$ch->consume(static function (Message $msg, Channel $ch, Client $c) use (&$time, &$count): void {
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
