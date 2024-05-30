<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240526143252 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE collection ADD author_id INT NOT NULL');
        $this->addSql('ALTER TABLE collection ADD CONSTRAINT FK_FC4D6532F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_FC4D6532F675F31B ON collection (author_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE collection DROP FOREIGN KEY FK_FC4D6532F675F31B');
        $this->addSql('DROP INDEX IDX_FC4D6532F675F31B ON collection');
        $this->addSql('ALTER TABLE collection DROP author_id');
    }
}
