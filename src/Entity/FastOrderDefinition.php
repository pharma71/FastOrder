<?php

namespace FastOrder\Entity;

use FastOrder\Entity\FastOrderEntity;
use FastOrder\Entity\FastOrderCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TextField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;

class FastOrderDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'fast_order';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return FastOrderEntity::class;
    }

    public function getCollectionClass(): string
    {
    return FastOrderCollection::class;
    }


    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            new StringField('id', 'id'),
            new StringField('session_id', 'sessionId'),
            new StringField('product_number', 'productNumber'),
            new IntField('quantity', 'quantity'),
            new CustomFields(),
            new CreatedAtField(),
            new UpdatedAtField(),
        ]);
    }
}

