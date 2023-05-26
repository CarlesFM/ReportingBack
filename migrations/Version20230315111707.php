<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230315111707 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE empleado (id INT AUTO_INCREMENT NOT NULL, dni VARCHAR(255) NOT NULL, nombre VARCHAR(255) DEFAULT NULL, apellidos VARCHAR(255) DEFAULT NULL, correo VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mes (id INT AUTO_INCREMENT NOT NULL, empleado_id INT DEFAULT NULL, fecha DATETIME DEFAULT NULL, INDEX IDX_6EC83E05952BE730 (empleado_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE registro (id INT AUTO_INCREMENT NOT NULL, mes_id INT DEFAULT NULL, entrada TIME DEFAULT NULL, salida TIME DEFAULT NULL, almuerzo_entrada TIME DEFAULT NULL, almuerzo_salida TIME DEFAULT NULL, comida_entrada TIME NOT NULL, comida_salida TIME DEFAULT NULL, INDEX IDX_397CA85BB4F0564A (mes_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mes ADD CONSTRAINT FK_6EC83E05952BE730 FOREIGN KEY (empleado_id) REFERENCES empleado (id)');
        $this->addSql('ALTER TABLE registro ADD CONSTRAINT FK_397CA85BB4F0564A FOREIGN KEY (mes_id) REFERENCES mes (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mes DROP FOREIGN KEY FK_6EC83E05952BE730');
        $this->addSql('ALTER TABLE registro DROP FOREIGN KEY FK_397CA85BB4F0564A');
        $this->addSql('DROP TABLE empleado');
        $this->addSql('DROP TABLE mes');
        $this->addSql('DROP TABLE registro');
    }
}
