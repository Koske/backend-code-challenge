<?php
	declare(strict_types=1);

	namespace App\Controller;

	use App\Constants\ErrorMessages;
	use App\Service\MessageService;
	use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\Routing\Annotation\Route;
	use Symfony\Component\HttpFoundation\JsonResponse;
	use Symfony\Component\Serializer\SerializerInterface;

	/**
	 * MessageController handles requests related to messages.
	 */
	class MessageController extends AbstractController
	{
		private SerializerInterface $serializer;
		private MessageService $messageService;

		public function __construct(SerializerInterface $serializer, MessageService $messageService)
		{
			$this->serializer = $serializer;
			$this->messageService = $messageService;
		}

		/**
		 * List all messages based on request parameters.
		 *
		 * @param Request $request
		 * @return JsonResponse
		 */
		#[Route('/messages', name: 'get_messages', methods: ['GET'])]
		public function getMessages(Request $request): JsonResponse
		{
			$status = $request->query->get('status');

			// Ensure that $status is either a string or null.
			if (!is_string($status) && !is_null($status)) {
				return $this->json([
					'error' => 'Invalid status type, must be a string or null.'
				], Response::HTTP_BAD_REQUEST);
			}

			$messages = $this->messageService->getMessages($status);
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
		 * @param Request $request
		 * @return Response
		 */
		#[Route('/messages', name: 'send_message', methods: ['POST'])]
		public function sendMessage(Request $request): Response
		{
			$data = json_decode($request->getContent(), true);

			// Ensure that $data is an array before accessing 'text'
			if (!is_array($data) || !isset($data['text']) || !is_string($data['text'])) {
				return new Response(ErrorMessages::TEXT_REQUIRED, Response::HTTP_BAD_REQUEST);
			}

			$isSent = $this->messageService->sendMessage($data['text']);

			if (!$isSent) {
				return new Response(ErrorMessages::FAILED_TO_SEND_MESSAGE, Response::HTTP_INTERNAL_SERVER_ERROR);
			}

			return new Response(ErrorMessages::MESSAGE_SUCCESSFULLY_SENT, Response::HTTP_OK);
		}

	}
