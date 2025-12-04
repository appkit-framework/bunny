<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Bunny\Test;

use Bunny\Channels;
use Bunny\Configuration;
use Bunny\Connection;
use Bunny\Protocol\Buffer;
use Bunny\Protocol\ProtocolReader;
use Bunny\Protocol\ProtocolWriter;
use Bunny\Test\Library\ClientHelper;
use Evenement\EventEmitterTrait;
use PHPUnit\Framework\TestCase;
use React\EventLoop\Loop;
use React\Promise\Deferred;
use React\Socket\ConnectionInterface;
use React\Stream\WritableStreamInterface;
use WyriHaximus\React\PHPUnit\RunTestsInFibersTrait;
use function React\Async\await;

class ConnectionTest extends TestCase
{
    use RunTestsInFibersTrait;

    private ClientHelper $helper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->helper = new ClientHelper();
    }

    public function testThrowOn(): void
    {
        // phpcs:disable
        $mockConnection = new class () implements ConnectionInterface {
            use EventEmitterTrait;

            /**
             * @return string
             */
            public function getRemoteAddress()
            {
                return '127.0.0.1:666';
            }

            /**
             * @return string
             */
            public function getLocalAddress()
            {
                return '127.0.0.1:666';
            }

            public function isReadable(): void
            {
                // TODO: Implement isReadable() method.
            }

            public function pause(): void
            {
                // TODO: Implement pause() method.
            }

            public function resume(): void
            {
                // TODO: Implement resume() method.
            }

            /**
             * @param array $options
             */
            public function pipe(WritableStreamInterface $dest, array $options = []): void
            {
                // TODO: Implement pipe() method.
            }

            public function close(): void
            {
                // TODO: Implement close() method.
            }

            public function isWritable(): void
            {
                // TODO: Implement isWritable() method.
            }

            /**
             * @param string $data
             */
            public function write($data): void
            {
                // TODO: Implement write() method.
            }

            /**
             * @param ?string $data
             */
            public function end($data = null): void
            {
                // TODO: Implement end() method.
            }
        };
        // phpcs:enable
        $connection = new Connection(
            $this->helper->createClient(),
            $mockConnection,
            new Buffer(),
            new Buffer(),
            new ProtocolReader(),
            new ProtocolWriter(),
            new Channels(),
            new Configuration(),
        );
        $mockConnection->emit(
            'data',
            [
                ''
            ],
        );
//        $connection->awaitAck(666);
//        $connection->awaitContentHeader(666);
    }
}
