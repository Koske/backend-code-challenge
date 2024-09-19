<?php

	declare(strict_types=1);

	namespace App\Tests\Repository;

	use App\Constants\ErrorMessages;
	use App\DataFixtures\AppFixtures;
	use App\Entity\Message;
	use App\Enum\MessageStatus;
	use App\Repository\MessageRepository;
	use Doctrine\ORM\EntityManagerInterface;
	use Doctrine\Common\DataFixtures\Loader;
	use Doctrine\Common\DataFixtures\Purger\ORMPurger;
	use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
	use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

	class MessageRepositoryTest extends KernelTestCase
	{
		private static ?EntityManagerInterface $entityManager = null;
		private static ?MessageRepository $messageRepository = null;

		public function setUp(): void
		{
			self::bootKernel();
			$container = self::getContainer();

			$entityManager = $container->get('doctrine.orm.entity_manager');
			$this->assertInstanceOf(EntityManagerInterface::class, $entityManager, ErrorMessages::EXPECTED_INSTANCE_OF_ENTITY_MANAGER);

			self::$entityManager = $entityManager;

			$repository = self::$entityManager->getRepository(Message::class);
			$this->assertInstanceOf(MessageRepository::class, $repository, 'Expected an instance of MessageRepository.');
			self::$messageRepository = $repository;

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

		public function testGetMessageByStatusWithNoStatus(): void
		{
			$messages = self::$messageRepository?->getMessages(null);

			$this->assertNotEmpty($messages, 'The messages array should not be empty.');

			$this->assertGreaterThanOrEqual(1, count($messages), 'There should be at least one message in the database.');

			$statuses = array_unique(array_map(fn($message) => $message->getStatus(), $messages));
			$this->assertGreaterThan(1, count($statuses), 'Messages should have varying statuses.');
		}

		public function testGetMessageByStatusWithValidStatus(): void
		{
			$status = MessageStatus::tryFrom('read');
			$messages = self::$messageRepository?->getMessages($status);

			$this->assertNotEmpty($messages, 'The messages array should not be empty for a valid status.');

			foreach ($messages as $message) {
				$this->assertEquals($status->value, $message->getStatus(), 'All messages should have the status "' . $status->value . '".');
			}
		}

		public function testGetMessageByStatusWithInvalidStatus(): void
		{
			$status = MessageStatus::tryFrom('invalid_status');

			$messages = self::$messageRepository?->getMessages($status);

			$this->assertNotEmpty($messages, 'The messages array should not be empty when status is invalid.');
		}

		public function testGetMessagesReturnsArrayOfMessages(): void
		{
			$messages = self::$messageRepository?->getMessages(null);

			$this->assertIsArray($messages, 'Expected an array of messages.');

			foreach ($messages as $message) {
				$this->assertInstanceOf(Message::class, $message, 'Each item in the array should be an instance of Message.');
			}
		}

		protected function tearDown(): void
		{
			if (self::$entityManager !== null) {
				$purger = new ORMPurger(self::$entityManager);
				$purger->purge();
			}

			parent::tearDown();
		}
	}
