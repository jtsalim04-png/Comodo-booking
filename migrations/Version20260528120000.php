<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260528120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add auth_type column to user table for Google vs local sign-in';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE user ADD auth_type VARCHAR(20) DEFAULT 'local' NOT NULL");
        $this->addSql("UPDATE user SET auth_type = 'local' WHERE auth_type IS NULL OR auth_type = ''");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP auth_type');
    }
}
