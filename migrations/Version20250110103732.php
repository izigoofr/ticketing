<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250110103732 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sandbox ADD tagged_users_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sandbox ADD CONSTRAINT FK_E6EAF167339EF075 FOREIGN KEY (tagged_users_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_E6EAF167339EF075 ON sandbox (tagged_users_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sandbox DROP FOREIGN KEY FK_E6EAF167339EF075');
        $this->addSql('DROP INDEX IDX_E6EAF167339EF075 ON sandbox');
        $this->addSql('ALTER TABLE sandbox DROP tagged_users_id');
    }
}
