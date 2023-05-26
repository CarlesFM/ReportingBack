<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230321095232 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mes DROP FOREIGN KEY FK_6EC83E05952BE730');
        $this->addSql('ALTER TABLE mes CHANGE empleado_id empleado_id INT NOT NULL');
        $this->addSql('ALTER TABLE mes ADD CONSTRAINT FK_6EC83E05952BE730 FOREIGN KEY (empleado_id) REFERENCES empleado (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mes DROP FOREIGN KEY FK_6EC83E05952BE730');
        $this->addSql('ALTER TABLE mes CHANGE empleado_id empleado_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mes ADD CONSTRAINT FK_6EC83E05952BE730 FOREIGN KEY (empleado_id) REFERENCES empleado (id)');
    }
}
