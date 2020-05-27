<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200527145847 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE code ADD buy_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE code ADD CONSTRAINT FK_771530984AFB9379 FOREIGN KEY (buy_id) REFERENCES buy (id)');
        $this->addSql('CREATE INDEX IDX_771530984AFB9379 ON code (buy_id)');
        $this->addSql('ALTER TABLE buy DROP FOREIGN KEY FK_CF83827727DAFE17');
        $this->addSql('DROP INDEX UNIQ_CF83827727DAFE17 ON buy');
        $this->addSql('ALTER TABLE buy DROP code_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE buy ADD code_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE buy ADD CONSTRAINT FK_CF83827727DAFE17 FOREIGN KEY (code_id) REFERENCES code (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CF83827727DAFE17 ON buy (code_id)');
        $this->addSql('ALTER TABLE code DROP FOREIGN KEY FK_771530984AFB9379');
        $this->addSql('DROP INDEX IDX_771530984AFB9379 ON code');
        $this->addSql('ALTER TABLE code DROP buy_id');
    }
}
