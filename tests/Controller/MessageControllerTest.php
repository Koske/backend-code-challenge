<?php

	declare(strict_types=1);

	namespace App\Tests\Controller;

	use App\Constants\ErrorMessages;
	use App\Message\SendMessage;
	use PHPUnit\Framework\MockObject\MockObject;
	use Symfony\Bundle\FrameworkBundle\KernelBrowser;
	use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
	use Doctrine\ORM\EntityManagerInterface;
	use Doctrine\Common\DataFixtures\Purger\ORMPurger;
	use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
	use Doctrine\Common\DataFixtures\Loader;
	use App\DataFixtures\AppFixtures;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\Messenger\MessageBusInterface;
	use Symfony\Component\Messenger\Envelope;

	class MessageControllerTest extends WebTestCase
	{
		private static ?EntityManagerInterface $entityManager = null;
		private KernelBrowser $client;
		private MockObject $messageBus;

		protected function setUp(): void
		{
			parent::setUp();

			$this->client = static::createClient();
			$container = $this->client->getContainer();
			$entityManager = $container->get('doctrine.orm.entity_manager');

			$this->assertInstanceOf(EntityManagerInterface::class, $entityManager, ErrorMessages::EXPECTED_INSTANCE_OF_ENTITY_MANAGER);

			self::$entityManager = $entityManager;

			$this->messageBus = $this->createMock(MessageBusInterface::class);
			$container->set(MessageBusInterface::class, $this->messageBus);

			self::loadFixtures();
		}

		private static function loadFixtures(): void
		{
			if (self::$entityManager === null) {
				throw new \RuntimeException(ErrorMessages::ENTITY_MANAGER_NOT_INITIALIZED);
			}

			$purger = new ORMPurger(self::$entityManager);
			$purger->purge();

			$fixtureLoader = new Loader();
			$fixtureLoader->addFixture(new AppFixtures());

			$executor = new ORMExecutor(self::$entityManager, $purger);
			$executor->execute($fixtureLoader->getFixtures(), true);
		}

		public function test_that_it_creates_a_message(): void
		{
			$data = ['text' => 'Sample message'];

			$this->messageBus
				->expects($this->once())
				->method('dispatch')
				->with($this->callback(function ($message) {
					return $message instanceof SendMessage && $message->getText() === 'Sample message';
				}))
				->willReturn(new Envelope(new SendMessage('Sample message')));

			$jsonContent = json_encode($data);
			$this->client->request('POST', '/messages', [], [], ['CONTENT_TYPE' => 'application/json'], $jsonContent !== false ? $jsonContent : '');

			$response = $this->client->getResponse();
			$this->assertTrue($response->isSuccessful());
			$this->assertEquals(ErrorMessages::MESSAGE_SUCCESSFULLY_SENT, $response->getContent());
		}

		public function test_that_it_retrieves_messages_with_expected_fields(): void
		{
			$this->client->request('GET', '/messages');

			$response = $this->client->getResponse();
			$this->assertEquals(200, $response->getStatusCode(), 'Expected status code 200');

			$content = $response->getContent();
			if ($content === false) {
				$this->fail('Failed to get response content.');
			}
			$data = json_decode($content, true);

			$this->assertIsArray($data, 'Response content is not valid JSON');
			$this->assertArrayHasKey('messages', $data, 'Response does not contain "messages" key');
			$this->assertIsArray($data['messages'], '"messages" should be an array');

			if (count($data['messages']) > 0) {
				$firstMessage = $data['messages'][0];

				$this->assertArrayHasKey('id', $firstMessage, 'Message does not contain "id" key');
				$this->assertArrayHasKey('uuid', $firstMessage, 'Message does not contain "uuid" key');
				$this->assertArrayHasKey('text', $firstMessage, 'Message does not contain "text" key');
				$this->assertArrayHasKey('status', $firstMessage, 'Message does not contain "status" key');
				$this->assertArrayHasKey('createdAt', $firstMessage, 'Message does not contain "createdAt" key');

				$this->assertIsInt($firstMessage['id'], '"id" should be an integer');
				$this->assertIsString($firstMessage['uuid'], '"uuid" should be a string');
				$this->assertIsString($firstMessage['text'], '"text" should be a string');
				$this->assertIsString($firstMessage['status'] ?? '', '"status" should be a string or null');
				$this->assertIsString($firstMessage['createdAt'], '"createdAt" should be a string');
			}
		}

		public function test_send_message_with_missing_text(): void
		{
			$data = [];
			$jsonContent = json_encode($data);

			if ($jsonContent === false) {
				$jsonContent = '';
			}

			$this->client->request('POST', '/messages', [], [], ['CONTENT_TYPE' => 'application/json'], $jsonContent);
			$response = $this->client->getResponse();
			$this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), 'Expected status code 400');
			$this->assertEquals(ErrorMessages::TEXT_REQUIRED, $response->getContent());
		}

		public function test_send_message_with_exception(): void
		{
			$data = ['text' => 'Sample message'];
			$jsonContent = json_encode($data);

			if ($jsonContent === false) {
				$jsonContent = '';
			}

			$this->messageBus
				->expects($this->once())
				->method('dispatch')
				->willThrowException(new \Exception('Dispatch error'));

			$this->client->request('POST', '/messages', [], [], ['CONTENT_TYPE' => 'application/json'], $jsonContent);

			$response = $this->client->getResponse();
			$this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode(), 'Expected status code 500');
			$this->assertEquals(ErrorMessages::FAILED_TO_SEND_MESSAGE, $response->getContent());
		}

		public function test_get_messages_without_status(): void
		{
			$this->client->request('GET', '/messages');

			$response = $this->client->getResponse();
			$this->assertEquals(200, $response->getStatusCode(), 'Expected status code 200');

			$content = $response->getContent();
			if ($content === false) {
				$this->fail('Failed to get response content.');
			}
			$data = json_decode($content, true);

			$this->assertIsArray($data, 'Response content is not valid JSON');
			$this->assertArrayHasKey('messages', $data, 'Response does not contain "messages" key');
			$this->assertIsArray($data['messages'], '"messages" should be an array');
		}

		protected function tearDown(): void
		{
			if (self::$entityManager) {
				$purger = new ORMPurger(self::$entityManager);
				$purger->purge();
			}

			parent::tearDown();
		}
	}
