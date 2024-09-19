<?php
declare(strict_types=1);

namespace App\Message;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Class SendMessage
 *
 * Represents a message to be sent with text content.
 */
class SendMessage
{
	/**
	 * @var string The text content of the message.
	 */
	private string $text;

	/**
	 * SendMessage constructor.
	 *
	 * @param string $text The text content of the message.
	 */
	public function __construct(string $text)
	{
		$this->text = $text;
	}

	/**
	 * Get the text content of the message.
	 *
	 * @return string
	 */
	public function getText(): string
	{
		return $this->text;
	}
}
