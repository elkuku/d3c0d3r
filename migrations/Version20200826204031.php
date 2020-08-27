<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200826204031 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE waypoint_reference (id INT AUTO_INCREMENT NOT NULL, waypoint_id INT NOT NULL, filename VARCHAR(255) NOT NULL, original_filename VARCHAR(255) NOT NULL, mime_type VARCHAR(255) NOT NULL, INDEX IDX_635D5F317BB1FD97 (waypoint_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE waypoint_reference ADD CONSTRAINT FK_635D5F317BB1FD97 FOREIGN KEY (waypoint_id) REFERENCES waypoint (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE waypoint_reference');
    }
}
