<?php declare(strict_types=1);

namespace FastOrder\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1736266121CreateFastOrderTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1736266121;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
        CREATE TABLE `fast_order` (
	    `id` VARCHAR(255) NOT NULL,
	    `session_id` VARCHAR(255) NOT NULL,
	    `product_number` VARCHAR(255) NOT NULL,
	    `quantity` INT NOT NULL,
	    `custom_fields` JSON NULL DEFAULT NULL,
	    `created_at` DATETIME(3) NOT NULL,
	    `updated_at` DATETIME NULL DEFAULT NULL,
	    PRIMARY KEY (`id`)
        )
        COLLATE="utf8mb4_unicode_ci"
        ENGINE=InnoDB;

        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        $connection->executeStatement('DROP TABLE IF EXISTS `fast_order`;');
    }
}
