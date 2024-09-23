<?php
	declare(strict_types=1);

	namespace App\Service;

	use App\Entity\Message;
	use App\Enum\MessageStatus;
	use App\Message\SendMessage;
	use App\Repository\MessageRepository;
	use Psr\Log\LoggerInterface;
	use Symfony\Component\Messenger\MessageBusInterface;

	class MessageService
	{
		private MessageRepository $messageRepository;
		private MessageBusInterface $messageBus;
		private LoggerInterface $logger;

		public function __construct(
			MessageRepository $messageRepository,
			MessageBusInterface $messageBus,
			LoggerInterface $logger
		) {
			$this->messageRepository = $messageRepository;
			$this->messageBus = $messageBus;
			$this->logger = $logger;
		}

		/**
		 * Get messages based on their status.
		 *
		 * @param string|null $status
		 * @return Message[]  // Specify that this method returns an array of Message objects.
		 */
		public function getMessages(?string $status): array
		{
			$messageStatus = null;

			if (is_string($status)) {
				$messageStatus = MessageStatus::tryFrom($status);
			}

			return $this->messageRepository->getMessages($messageStatus);
		}

		/**
		 * Send a new message.
		 *
		 * @param string $text
		 * @return bool
		 */
		public function sendMessage(string $text): bool
		{
			try {
				$this->messageBus->dispatch(new SendMessage($text));
				return true;
			} catch (\Throwable $e) {
				$this->logger->error("Failed to send message: " . $e->getMessage());
				return false;
			}
		}
	}
