<?php

declare(strict_types=1);

use Bunny\Channel;
use Bunny\ChannelInterface;
use Bunny\Client;
use Bunny\Message;
use React\Promise\Deferred;
use function React\Async\async;
use function React\Async\await;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

// phpcs:disabled
class FibonacciRpcClient
{
    private Client $client;

    private ChannelInterface $channel;

    public function __construct()
    {
        $this->client = new Client();
        $this->channel = $this->client->channel();
    }

    public function close(): void
    {
        $this->client->disconnect();
    }

    public function call(int $n): int
    {
        $corrId = uniqid();
        $response = new Deferred();
        $responseQueue = $this->channel->queueDeclare('', false, false, true);
        $subscription = $this->channel->consume(
            async(static function (Message $message, Channel $channel, Client $client) use (&$response, $corrId, &$subscription): void {
                if ($message->getHeader('correlation_id') !== $corrId) {
                    return;
                }

                $response->resolve($message->content);
                $channel->cancel($subscription->consumerTag);
            }),
            $responseQueue->queue,
        );
        $this->channel->publish(
            (string) $n,
            [
                'correlation_id' => $corrId,
                'reply_to' => $responseQueue->queue,
            ],
            '',
            'rpc_queue',
        );

        return (int) await($response->promise());
    }
}

$fibonacciRpc = new FibonacciRpcClient();
$response = $fibonacciRpc->call(30);
echo ' [.] Got ' . $response . PHP_EOL;
$fibonacciRpc->close();
