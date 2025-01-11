<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250110104703 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sandbox_user (sandbox_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_692F7DD166A1C3E9 (sandbox_id), INDEX IDX_692F7DD1A76ED395 (user_id), PRIMARY KEY(sandbox_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sandbox_user ADD CONSTRAINT FK_692F7DD166A1C3E9 FOREIGN KEY (sandbox_id) REFERENCES sandbox (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sandbox_user ADD CONSTRAINT FK_692F7DD1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sandbox DROP FOREIGN KEY FK_E6EAF167339EF075');
        $this->addSql('DROP INDEX IDX_E6EAF167339EF075 ON sandbox');
        $this->addSql('ALTER TABLE sandbox DROP tagged_users_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sandbox_user DROP FOREIGN KEY FK_692F7DD166A1C3E9');
        $this->addSql('ALTER TABLE sandbox_user DROP FOREIGN KEY FK_692F7DD1A76ED395');
        $this->addSql('DROP TABLE sandbox_user');
        $this->addSql('ALTER TABLE sandbox ADD tagged_users_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sandbox ADD CONSTRAINT FK_E6EAF167339EF075 FOREIGN KEY (tagged_users_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_E6EAF167339EF075 ON sandbox (tagged_users_id)');
    }
}
