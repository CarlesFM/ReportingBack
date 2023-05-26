<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230321095450 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE registro DROP FOREIGN KEY FK_397CA85BB4F0564A');
        $this->addSql('ALTER TABLE registro CHANGE mes_id mes_id INT NOT NULL');
        $this->addSql('ALTER TABLE registro ADD CONSTRAINT FK_397CA85BB4F0564A FOREIGN KEY (mes_id) REFERENCES mes (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE registro DROP FOREIGN KEY FK_397CA85BB4F0564A');
        $this->addSql('ALTER TABLE registro CHANGE mes_id mes_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE registro ADD CONSTRAINT FK_397CA85BB4F0564A FOREIGN KEY (mes_id) REFERENCES mes (id)');
    }
}
