<?php

declare(strict_types=1);

namespace Bunny;

use function getmypid;
use function microtime;
use function printf;

require_once __DIR__ . '/../vendor/autoload.php';

$c = new Client();
$c->connect();
$ch = $c->channel();

$ch->queueDeclare('bench_queue');
$ch->exchangeDeclare('bench_exchange');
$ch->queueBind('bench_exchange', 'bench_queue');

$body = <<<'EOT'
abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyza
EOT;

$time = microtime(true);
$max = isset($argv[1]) ? (int) $argv[1] : 1;

for ($i = 0; $i < $max; $i++) {
    $ch->publish($body, [], 'bench_exchange');
}

$runTime = microtime(true) - $time;
printf("Produce: Pid: %s, Time: %.6f, Msg/sec: %.0f\n", getmypid(), $runTime, 1 / $runTime * $max);

$ch->publish('quit', [], 'bench_exchange');

$c->disconnect();
