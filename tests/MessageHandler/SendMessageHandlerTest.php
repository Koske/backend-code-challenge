<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\Constants\ErrorMessages;
use App\Entity\Message;
use App\Enum\MessageStatus;
use App\Message\SendMessage;
use App\MessageHandler\SendMessageHandler;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class SendMessageHandlerTest extends TestCase
{
	private MockObject $entityManager;
	private MockObject $logger;
	private SendMessageHandler $handler;

	protected function setUp(): void
	{
		$this->entityManager = $this->createMock(EntityManagerInterface::class);
		$this->logger = $this->createMock(LoggerInterface::class);

		$this->handler = new SendMessageHandler($this->entityManager, $this->logger);
	}

	public function testInvokeSuccess(): void
	{
		$sendMessage = new SendMessage('Test message');
		$message = new Message();
		$message->setText('Test message');
		$message->setStatus(MessageStatus::SENT);

		$this->entityManager->expects(self::once())
			->method('persist')
			->with(self::callback(function ($arg) use ($message) {
				return $arg instanceof Message
					&& $arg->getText() === $message->getText()
					&& $arg->getStatus() === $message->getStatus();
			}));

		$this->entityManager->expects(self::once())
			->method('flush');

		$this->logger->expects(self::never())
			->method('error');

		$this->handler->__invoke($sendMessage);
	}

	public function testInvokeFailure(): void
	{
		$sendMessage = new SendMessage('Test message');

		$this->entityManager->expects(self::once())
			->method('persist')
			->willThrowException(new \Exception('Database error'));

		$this->entityManager->expects(self::never())
			->method('flush');

		$this->logger->expects(self::once())
			->method('error')
			->with(
				ErrorMessages::FAILED_TO_PERSIST .': Database error',
				self::arrayHasKey('exception')
			);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage(ErrorMessages::FAILED_TO_PERSIST);

		$this->handler->__invoke($sendMessage);
	}

	public function testInvokeWithEmptyMessageText(): void
	{
		$sendMessage = new SendMessage('');

		$this->entityManager->expects(self::once())
			->method('persist')
			->with(self::callback(function ($arg) {
				return $arg instanceof Message && $arg->getText() === '';
			}));

		$this->entityManager->expects(self::once())
			->method('flush');

		$this->logger->expects(self::never())
			->method('error');

		$this->handler->__invoke($sendMessage);
	}

	public function testLoggerExceptionDetails(): void
	{
		$sendMessage = new SendMessage('Test message');

		$this->entityManager->expects(self::once())
			->method('persist')
			->willThrowException(new \Exception('Database error'));

		$this->logger->expects(self::once())
			->method('error')
			->with(
				ErrorMessages::FAILED_TO_PERSIST . ': Database error',
				self::callback(function ($context) {
					return isset($context['exception']) && $context['message_text'] === 'Test message';
				})
			);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage(ErrorMessages::FAILED_TO_PERSIST);

		$this->handler->__invoke($sendMessage);
	}
}
