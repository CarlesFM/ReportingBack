<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230411082742 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE empleado ADD empresas_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE empleado ADD CONSTRAINT FK_D9D9BF52602B00EE FOREIGN KEY (empresas_id) REFERENCES empresas (id)');
        $this->addSql('CREATE INDEX IDX_D9D9BF52602B00EE ON empleado (empresas_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE empleado DROP FOREIGN KEY FK_D9D9BF52602B00EE');
        $this->addSql('DROP INDEX IDX_D9D9BF52602B00EE ON empleado');
        $this->addSql('ALTER TABLE empleado DROP empresas_id');
    }
}
