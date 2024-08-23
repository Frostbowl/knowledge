<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240823181421 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_lessons (user_id INT NOT NULL, lessons_id INT NOT NULL, INDEX IDX_674F06D3A76ED395 (user_id), INDEX IDX_674F06D3FED07355 (lessons_id), PRIMARY KEY(user_id, lessons_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_lessons ADD CONSTRAINT FK_674F06D3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_lessons ADD CONSTRAINT FK_674F06D3FED07355 FOREIGN KEY (lessons_id) REFERENCES lessons (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_lessons DROP FOREIGN KEY FK_674F06D3A76ED395');
        $this->addSql('ALTER TABLE user_lessons DROP FOREIGN KEY FK_674F06D3FED07355');
        $this->addSql('DROP TABLE user_lessons');
    }
}
