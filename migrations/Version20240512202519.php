<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240512202519 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE collections ADD name VARCHAR(255) NOT NULL, ADD description LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE item ADD collection_id INT NOT NULL, ADD name VARCHAR(255) NOT NULL, ADD author VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E514956FD FOREIGN KEY (collection_id) REFERENCES collections (id)');
        $this->addSql('CREATE INDEX IDX_1F1B251E514956FD ON item (collection_id)');
        $this->addSql('ALTER TABLE tag ADD name VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE collections DROP name, DROP description');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251E514956FD');
        $this->addSql('DROP INDEX IDX_1F1B251E514956FD ON item');
        $this->addSql('ALTER TABLE item DROP collection_id, DROP name, DROP author');
        $this->addSql('ALTER TABLE tag DROP name');
    }
}
