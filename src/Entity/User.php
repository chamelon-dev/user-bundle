<?php

namespace Pantheon\UserBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="`user`")
 * @ORM\Entity(repositoryClass="Pantheon\UserBundle\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    /**
     * Уникальный логин пользователя.
     *
     * @var string
     * @ORM\Column(type="string", unique=true, nullable=false)
     */
    private $username;

    /**
     * Email - также уникальный идентификатор пользователя.
     *
     * @var string
     * @ORM\Column(type="string", unique=true, nullable=false)
     */
    private $email;

    /**
     * Любые дополнительные данные.
     * @ORM\Column(type="json", nullable=true)
     */
    private $data = [];

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $roles = [];

    /**
     * Хэш пароля.
     * @var string
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var DateTime
     */
    private $lastLogin;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isActive = true;

    /**
     * @ORM\ManyToMany(targetEntity=Role::class)
     */
    private $role;

    /**
     * Имя пользователя.
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $name = null;

    /**
     * Фамилия пользователя.
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $lastname = null;

    /**
     * Отчество пользователя.
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $patronymic = null;

    /**
     * Место работы.
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $workplace = null;

    /**
     * Должность.
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $duty = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var DateTime
     */
    private $birthdate = null;

    /**
     * Номер телефона.
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $phone = null;

    public function __toString()
    {
        return $this->getFio() ? : $this->getUsername();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return (string)$this->username;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function getRole() : Collection
    {
        return $this->role;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function setRole(array $roles): self
    {
        $this->role = $roles;
        return $this;
    }


    public function getPassword(): string
    {
        return (string)$this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function hasRole(string $role) : bool
    {
        return in_array($role, $this->getRoles(), true);
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive)  : self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getLastLogin(): ?DateTime
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?DateTime $lastLogin) : self
    {
        $this->lastLogin = $lastLogin;
        return $this;
    }

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var DateTime
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var DateTime
     */
    protected $updatedAt;

    /**
     * Обновление даты перед созданием сущности.
     * @ORM\PrePersist
     */
    public function updateCreatedAt()
    {
        $this->createdAt = new DateTime("now");
        $this->updatedAt = new DateTime("now");
    }

    /**
     * Обновление даты при обновлении сущности.
     * @ORM\PreUpdate
     */
    public function updateUpdatedAt()
    {
        $this->updatedAt = new DateTime("now");
    }

    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $value): self
    {
        $this->name = $value;
        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $value): self
    {
        $this->lastname = $value;
        return $this;
    }

    public function getPatronymic(): ?string
    {
        return $this->patronymic;
    }

    public function setPatronymic(?string $value): self
    {
        $this->patronymic = $value;
        return $this;
    }

    public function getWorkplace(): ?string
    {
        return $this->workplace;
    }

    public function setWorkplace(?string $value): self
    {
        $this->workplace = $value;
        return $this;
    }

    public function getDuty(): ?string
    {
        return $this->duty;
    }

    public function setDuty(?string $value): self
    {
        $this->duty = $value;
        return $this;
    }

    public function getBirthdate(): ?DateTime
    {
        return $this->birthdate;
    }

    public function setBirthdate(?DateTime $value): self
    {
        $this->birthdate = $value;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $value): self
    {
        $this->phone = $value;
        return $this;
    }

    public function getFio() : ?string
    {
        $fio = null;
        if ($lastname = $this->getLastname()) {
            $fio = implode(' ', [$this->getLastname(), $this->getName(), $this->getPatronymic()]);
        }
        return $fio;
    }
}
