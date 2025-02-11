<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250110115638 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C66A1C3E9');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C66A1C3E9 FOREIGN KEY (sandbox_id) REFERENCES sandbox (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C66A1C3E9');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C66A1C3E9 FOREIGN KEY (sandbox_id) REFERENCES sandbox (id)');
    }
}
