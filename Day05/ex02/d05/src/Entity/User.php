<?php

namespace App\Entity;

class User
{
    protected string $username;
    protected string $name;
    protected string $email;
    protected bool $enable;
    protected ?\DateTimeInterface $birthdate;
    protected string $address;

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getName(): string
    {
    	return $this->name;
    }

    public function setName(string $name): void
    {
    	$this->name = $name;
    }

    public function getEmail(): string
    {
    	return $this->email;
    }

    public function setEmail(string $email): void
    {
    	$this->email = $email;
    }

    public function getEnable(): bool
    {
    	return $this->enable;
    }

    public function setEnable(bool $enable): void
    {
    	$this->enable = $enable;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTimeInterface $birthdate): void
    {
        $this->birthdate = $birthdate;
    }

    public function getAddress(): string
    {
    	return $this->address;
    }

    public function setAddress(string $address): void
    {
    	$this->address = $address;
    }
}
