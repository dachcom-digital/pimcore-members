<?php

namespace MembersBundle\Restriction;

use Pimcore\Model\AbstractModel;

/**
 * @method \MembersBundle\Restriction\Dao getDao()
 */
class Restriction extends AbstractModel
{
    public ?int $id = null;
    public ?string $ctype = null;
    public int $targetId = 0;
    public bool $isInherited = false;
    public bool $inherit = false;
    public array $relatedGroups = [];

    public static function getById(int $id): Restriction
    {
        $obj = new self();
        $obj->getDao()->getById($id);

        return $obj;
    }

    public static function getByTargetId(int $id, string $cType = 'page'): Restriction
    {
        $obj = new self();
        $obj->getDao()->getByField('targetId', (int) $id, $cType);

        return $obj;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = (int) $id;

        return $this;
    }

    public function getCtype(): string
    {
        return $this->ctype;
    }

    public function setCtype(string $cType): static
    {
        $this->ctype = $cType;

        return $this;
    }

    public function getTargetId(): string
    {
        return $this->targetId;
    }

    public function setTargetId(int $targetId): static
    {
        $this->targetId = (int) $targetId;

        return $this;
    }

    public function getRelatedGroups(): array
    {
        return $this->relatedGroups;
    }

    public function setRelatedGroups(array $relatedGroups): static
    {
        $this->relatedGroups = array_map('intval', $relatedGroups);

        return $this;
    }

    public function getIsInherited(): bool
    {
        return $this->isInherited;
    }

    public function isInherited(): bool
    {
        return $this->getIsInherited();
    }

    public function setIsInherited(bool $isInherited): static
    {
        $this->isInherited = $isInherited;

        return $this;
    }

    public function getInherit(): bool
    {
        return $this->inherit;
    }

    public function setInherit(bool $inherit): static
    {
        $this->inherit = (bool) $inherit;

        return $this;
    }
}
