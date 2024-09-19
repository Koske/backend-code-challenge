<?php

	namespace App\Repository;

	use App\Constants\ErrorMessages;
	use App\Entity\Message;
	use App\Enum\MessageStatus;
	use App\Repository\Interface\MessageRepositoryInterface;
	use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
	use Doctrine\Persistence\ManagerRegistry;

	/**
	 * @extends ServiceEntityRepository<Message>
	 *
	 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
	 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
	 * @method Message[]    findAll()
	 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
	 */
	class MessageRepository extends ServiceEntityRepository implements MessageRepositoryInterface
	{
		public function __construct(ManagerRegistry $registry)
		{
			parent::__construct($registry, Message::class);
		}

		/**
		 * @param MessageStatus|null $status
		 * @return Message[] Array of Message entities
		 */
		public function getMessages(?MessageStatus $status): array
		{
			$qb = $this->createQueryBuilder('m');

			if ($status?->value) {
				$qb->where('m.status = :status')
					->setParameter('status', $status->value);
			}

			$result = $qb->getQuery()->getResult();

			if (!is_array($result)) {
				throw new \RuntimeException(ErrorMessages::EXPECTED_ARRAY_MESSAGES);
			}

			return $result;
		}
	}
