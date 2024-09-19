<?php

	namespace App\Repository\Interface;

	use App\Entity\Message;
	use App\Enum\MessageStatus;

	interface MessageRepositoryInterface
	{
		/**
		 * @param MessageStatus|null $status
		 * @return Message[] Array of Message entities
		 */
		public function getMessages(?MessageStatus $status): array;
	}
