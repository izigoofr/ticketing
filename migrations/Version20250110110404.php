<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250110110404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project ADD sandboxes_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EECEE3C7F3 FOREIGN KEY (sandboxes_id) REFERENCES sandbox (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2FB3D0EECEE3C7F3 ON project (sandboxes_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EECEE3C7F3');
        $this->addSql('DROP INDEX UNIQ_2FB3D0EECEE3C7F3 ON project');
        $this->addSql('ALTER TABLE project DROP sandboxes_id');
    }
}
