<?php
declare(strict_types=1);

namespace App\Controller;

use App\Constants\ErrorMessages;
use App\Enum\MessageStatus;
use App\Message\SendMessage;
use App\Repository\MessageRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * MessageController handles requests related to messages.
 */
class MessageController extends AbstractController
{
	private LoggerInterface $logger;
	private SerializerInterface $serializer;

	public function __construct(LoggerInterface $logger, SerializerInterface $serializer)
	{
		$this->logger = $logger;
		$this->serializer = $serializer;
	}

	/**
	 * List all messages based on request parameters.
	 *
	 *
	 * @param Request $request
	 * @param MessageRepository $messageRepository
	 * @return JsonResponse
	 */
	#[Route('/messages', name: 'get_messages', methods: ['GET'])]
	public function getMessages(Request $request, MessageRepository $messageRepository): JsonResponse
	{
		$status = $request->query->get('status');

		if (is_string($status)) {
			$messageStatus = MessageStatus::tryFrom($status);
		}

		$messages = $messageRepository->getMessages($messageStatus ?? null);

		$formattedMessages = $this->serializer->serialize($messages, 'json', ['groups' => 'message:read']);
		$formattedMessagesArray = json_decode($formattedMessages, true);
		if (!is_array($formattedMessagesArray)) {
			$formattedMessagesArray = [];
		}

		return $this->json([
			'messages' => $formattedMessagesArray,
		]);
	}


	/**
	 * Dispatch a new message to the message bus.
	 *
	 *
	 * @param Request $request
	 * @param MessageBusInterface $messageBus
	 * @return Response
	 */

	#[Route('/messages', name: 'send_message', methods: ['POST'])]
	public function sendMessage(Request $request, MessageBusInterface $messageBus): Response
	{
		$data = json_decode($request->getContent(), true);

		if (is_array($data) && isset($data['text'])) {
			$text = $data['text'];
		} else {
			return new Response(ErrorMessages::TEXT_REQUIRED, Response::HTTP_BAD_REQUEST);
		}

		try {
			$messageBus->dispatch(new SendMessage($text));
		} catch (\Throwable $e) {
			$this->logger->error(ErrorMessages::FAILED_TO_SEND_MESSAGE . ":".$e->getMessage());

			return new Response(ErrorMessages::FAILED_TO_SEND_MESSAGE, Response::HTTP_INTERNAL_SERVER_ERROR);
		}

		return new Response(ErrorMessages::MESSAGE_SUCCESSFULLY_SENT, Response::HTTP_OK);
	}
}
