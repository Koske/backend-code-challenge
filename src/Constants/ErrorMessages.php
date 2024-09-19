<?php
	declare(strict_types=1);

	namespace App\Constants;

	/**
	 * Class ErrorMessages
	 * Provides constants for error messages used throughout the application.
	 */
	class ErrorMessages
	{
		public const TEXT_REQUIRED = 'Text is required';
		public const FAILED_TO_SEND_MESSAGE = 'Failed to send message';
		public const MESSAGE_SUCCESSFULLY_SENT = 'Message successfully sent';
		public const FAILED_TO_PERSIST = 'Failed to persist message';
		public const EXPECTED_ARRAY_MESSAGES = 'Expected an array of Message entities.';
		public const EXPECTED_INSTANCE_OF_ENTITY_MANAGER = 'Expected an instance of EntityManagerInterface';
		public const ENTITY_MANAGER_NOT_INITIALIZED = 'EntityManager is not initialized.';
	}
