<?php

namespace Pantheon\UserBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="permission")
 * @ORM\Entity(repositoryClass="Pantheon\UserBundle\Repository\PermissionRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Permission
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    /**
     * Машинное название пермишна, например, "Can restart query".
     * @ORM\Column(type="string", unique=true)
     */
    private $name;

    /**
     * Русскоязычное название пермишна, например, "Возможность перезапуска запросов".
     * @ORM\Column(type="string", nullable=true)
     */
    private $title;

    /**
     * Не обязательное описание.
     * @ORM\Column(type="string", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity=Role::class, mappedBy="permissions")
     */
    private $roles;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }


    public function getId()
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return Collection|Role[]
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
            $role->addPermission($this);
        }
        return $this;
    }

    public function removeRole(Role $role): self
    {
        if ($this->roles->removeElement($role)) {
            $role->removePermission($this);
        }
        return $this;
    }

    /////////////////////////////////////////////////
    // работа с датой и временем создания/обновления

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
}
