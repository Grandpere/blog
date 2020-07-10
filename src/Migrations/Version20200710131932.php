<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200710131932 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE view (id INT AUTO_INCREMENT NOT NULL, article_id INT NOT NULL, user_logged_id INT DEFAULT NULL, viewed_at DATETIME NOT NULL, client_ip VARCHAR(255) NOT NULL, user_agent VARCHAR(255) NOT NULL, INDEX IDX_FEFDAB8E7294869C (article_id), INDEX IDX_FEFDAB8E7203902F (user_logged_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE view ADD CONSTRAINT FK_FEFDAB8E7294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE view ADD CONSTRAINT FK_FEFDAB8E7203902F FOREIGN KEY (user_logged_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE article DROP views');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE view');
        $this->addSql('ALTER TABLE article ADD views INT NOT NULL');
    }
}
