<?php

declare(strict_types = 1);

namespace Symfony\Component\Notifier\Bridge\Iterable\Tests;

use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\Notifier\Bridge\Iterable\IterableTransport;
use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Test\TransportTestCase;
use Symfony\Component\Notifier\Transport\AbstractTransport;
use Symfony\Component\Notifier\Transport\TransportInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class IterableTransportTest extends TransportTestCase
{
    public function createTransport(?HttpClientInterface $client = null): AbstractTransport
    {
        return (new IterableTransport('testToken', 'testCampaignId', $client ?? $this->createMock(HttpClientInterface::class)))->setHost('host.test');
    }

    /**
     * @return iterable<array<string|TransportInterface>>
     */
    public function toStringProvider(): iterable
    {
        yield ['iterable://host.test', $this->createTransport()];
    }

    /**
     * @return iterable<array<MessageInterface>>
     */
    public function supportedMessagesProvider(): iterable
    {
        yield [new ChatMessage('Hello!')];
    }

    /**
     * @return iterable<array<MessageInterface>>
     */
    public function unsupportedMessagesProvider(): iterable
    {
        yield [new SmsMessage('0611223344', 'Hello!')];
        yield [$this->createMock(MessageInterface::class)];
    }

    public function testSendWithErrorResponseThrows(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->exactly(2))
            ->method('getStatusCode')
            ->willReturn(400)
        ;
        $response->expects($this->once())
            ->method('getContent')
            ->willReturn(json_encode(['msg' => 'testDescription', 'code' => 'testErrorCode']))
        ;

        $client = new MockHttpClient(static fn (): ResponseInterface => $response);

        $transport = $this->createTransport($client);

        $this->expectException(TransportException::class);
        $this->expectExceptionMessageMatches('/testDescription.+testErrorCode/');

        $transport->send(new ChatMessage('testMessage'));
    }

    public function testSuccessfulSend(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->exactly(2))
            ->method('getStatusCode')
            ->willReturn(200)
        ;
        $response->expects($this->once())
            ->method('getContent')
            ->willReturn(json_encode(['msg' => 'testDescription', 'code' => 'Success']))
        ;

        $client = new MockHttpClient(static fn (): ResponseInterface => $response);

        $transport = $this->createTransport($client);
        try {
            $transport->send(new ChatMessage('testMessage'));
        } catch (TransportException $exception) {
            $this->fail('TransportException thrown');
        }
    }
}
