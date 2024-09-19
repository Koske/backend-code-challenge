<?php
declare(strict_types=1);

namespace App\MessageHandler;

use App\Constants\ErrorMessages;
use App\Entity\Message;
use App\Enum\MessageStatus;
use App\Message\SendMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Handles the sending of messages by persisting them to the database.
 */
#[AsMessageHandler]
class SendMessageHandler
{
	/**
	 * @param EntityManagerInterface $manager The entity manager used for persistence.
	 * @param LoggerInterface $logger The logger used for logging errors.
	 */
	public function __construct(
		private EntityManagerInterface $manager,
		private LoggerInterface $logger
	) {
	}

	/**
	 * Handles the SendMessage message by creating and persisting a Message entity.
	 *
	 * @param SendMessage $sendMessage The message to be processed.
	 */
	public function __invoke(SendMessage $sendMessage): void
	{
		$message = new Message();
		$message->setText($sendMessage->getText());
		$message->setStatus(MessageStatus::SENT);

		try {
			$this->manager->persist($message);
			$this->manager->flush();
		} catch (\Exception $e) {
			$this->logger->error(ErrorMessages::FAILED_TO_PERSIST . ': ' . $e->getMessage(), [
				'exception' => $e,
				'message_text' => $sendMessage->getText(),
			]);

			throw new \RuntimeException(ErrorMessages::FAILED_TO_PERSIST, 0, $e);
		}
	}
}
