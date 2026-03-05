<?php

namespace App\Entity;

class Message
{
	protected string $message;
	protected bool $isTimestamp;

	public function getMessage(): string
	{
		return $this->message;
	}

	public function setMessage(string $message): void
	{
		$this->message = $message;
	}

	public function getIsTimestamp(): bool
	{
		return $this->isTimestamp;
	}

	public function setIsTimestamp(bool $isTimestamp)
	{
		$this->isTimestamp = $isTimestamp; 
	}
}
