<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

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

    public static function getById(int $id): self
    {
        $obj = new self();
        $obj->getDao()->getById($id);

        return $obj;
    }

    public static function getByTargetId(int $id, string $cType = 'page'): self
    {
        $obj = new self();
        $obj->getDao()->getByField('targetId', $id, $cType);

        return $obj;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getCtype(): ?string
    {
        return $this->ctype;
    }

    public function setCtype(string $cType): self
    {
        $this->ctype = $cType;

        return $this;
    }

    public function getTargetId(): int
    {
        return $this->targetId;
    }

    public function setTargetId(int $targetId): self
    {
        $this->targetId = $targetId;

        return $this;
    }

    public function getRelatedGroups(): array
    {
        return $this->relatedGroups;
    }

    public function setRelatedGroups(array $relatedGroups): self
    {
        $this->relatedGroups = array_map('intval', $relatedGroups);

        return $this;
    }

    public function isInherited(): bool
    {
        return $this->getIsInherited();
    }

    public function getIsInherited(): bool
    {
        return $this->isInherited;
    }

    public function setIsInherited(bool $isInherited): self
    {
        $this->isInherited = $isInherited;

        return $this;
    }

    public function getInherit(): bool
    {
        return $this->inherit;
    }

    public function setInherit(bool $inherit): self
    {
        $this->inherit = $inherit;

        return $this;
    }
}
