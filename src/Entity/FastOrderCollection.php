<?php

namespace FastOrder\Entity;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                     add(FastOrderEntity $entity)
 * @method void                     set(string $key, FastOrderEntity $entity)
 * @method FastOrderEntity[]        getIterator()
 * @method FastOrderEntity[]        getElements()
 * @method FastOrderEntity|null     get(string $key)
 * @method FastOrderEntity|null     first()
 * @method FastOrderEntity|null     last()
 */
class FastOrderCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return FastOrderEntity::class;
    }
}

