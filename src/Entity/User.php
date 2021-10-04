<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Serializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields="email", message="This email address is already in use")
 */
class User implements UserInterface, Serializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private string $role = 'ROLE_USER';

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $googleId;

    /**
     * {@inheritdoc}
     * @return null
     */
    public function eraseCredentials(): void
    {
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param string $role
     *
     * @return $this
     */
    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * {@inheritdoc}
     * @return (Role|string)[] The user roles
     */
    public function getRoles(): array
    {
        return [$this->getRole()];
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->email;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * @return null
     */
    public function getSalt(): void
    {
    }

    /**
     * String representation of object
     *
     * @link  http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize(): string
    {
        return serialize(
            [
                $this->id,
                $this->email,
            ]
        );
    }

    /**
     * Constructs the object
     *
     * @link  http://php.net/manual/en/serializable.unserialize.php
     *
     * @param string $serialized The string representation of the object.
     *
     * @return void
     */
    public function unserialize($serialized): void
    {
        list (
            $this->id,
            $this->email,
            )
            = unserialize($serialized);
    }

    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function setGoogleId(?string $googleId): self
    {
        $this->googleId = $googleId;

        return $this;
    }
}
