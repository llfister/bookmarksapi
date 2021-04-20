<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210415130851 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Feat: add bookmark & keyword';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bookmark (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', title VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, created_date DATETIME NOT NULL, height INT NOT NULL, width INT NOT NULL, duration INT DEFAULT NULL, author VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bookmark_keyword (bookmark_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', keyword_id INT NOT NULL, INDEX IDX_374B1A0092741D25 (bookmark_id), INDEX IDX_374B1A00115D4552 (keyword_id), PRIMARY KEY(bookmark_id, keyword_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE keyword (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE bookmark_keyword ADD CONSTRAINT FK_374B1A0092741D25 FOREIGN KEY (bookmark_id) REFERENCES bookmark (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bookmark_keyword ADD CONSTRAINT FK_374B1A00115D4552 FOREIGN KEY (keyword_id) REFERENCES keyword (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bookmark_keyword DROP FOREIGN KEY FK_374B1A0092741D25');
        $this->addSql('ALTER TABLE bookmark_keyword DROP FOREIGN KEY FK_374B1A00115D4552');
        $this->addSql('DROP TABLE bookmark');
        $this->addSql('DROP TABLE bookmark_keyword');
        $this->addSql('DROP TABLE keyword');
    }
}
