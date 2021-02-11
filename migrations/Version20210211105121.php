<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210211105121 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_5F9E962AA76ED395');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_5F9E962AB281BE2E');
        $this->addSql('ALTER TABLE comment CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('DROP INDEX idx_5f9e962aa76ed395 ON comment');
        $this->addSql('CREATE INDEX IDX_9474526CA76ED395 ON comment (user_id)');
        $this->addSql('DROP INDEX idx_5f9e962ab281be2e ON comment');
        $this->addSql('CREATE INDEX IDX_9474526CB281BE2E ON comment (trick_id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_5F9E962AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_5F9E962AB281BE2E FOREIGN KEY (trick_id) REFERENCES trick (id)');
        $this->addSql('ALTER TABLE trick DROP FOREIGN KEY FK_E1D902C112469DE2');
        $this->addSql('ALTER TABLE trick DROP FOREIGN KEY FK_E1D902C1A76ED395');
        $this->addSql('DROP INDEX idx_e1d902c1a76ed395 ON trick');
        $this->addSql('CREATE INDEX IDX_D8F0A91EA76ED395 ON trick (user_id)');
        $this->addSql('DROP INDEX idx_e1d902c112469de2 ON trick');
        $this->addSql('CREATE INDEX IDX_D8F0A91E12469DE2 ON trick (category_id)');
        $this->addSql('ALTER TABLE trick ADD CONSTRAINT FK_E1D902C112469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE trick ADD CONSTRAINT FK_E1D902C1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('DROP INDEX uniq_1483a5e9e7927c74 ON user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CA76ED395');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CB281BE2E');
        $this->addSql('ALTER TABLE comment CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('DROP INDEX idx_9474526ca76ed395 ON comment');
        $this->addSql('CREATE INDEX IDX_5F9E962AA76ED395 ON comment (user_id)');
        $this->addSql('DROP INDEX idx_9474526cb281be2e ON comment');
        $this->addSql('CREATE INDEX IDX_5F9E962AB281BE2E ON comment (trick_id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CB281BE2E FOREIGN KEY (trick_id) REFERENCES trick (id)');
        $this->addSql('ALTER TABLE trick DROP FOREIGN KEY FK_D8F0A91EA76ED395');
        $this->addSql('ALTER TABLE trick DROP FOREIGN KEY FK_D8F0A91E12469DE2');
        $this->addSql('DROP INDEX idx_d8f0a91ea76ed395 ON trick');
        $this->addSql('CREATE INDEX IDX_E1D902C1A76ED395 ON trick (user_id)');
        $this->addSql('DROP INDEX idx_d8f0a91e12469de2 ON trick');
        $this->addSql('CREATE INDEX IDX_E1D902C112469DE2 ON trick (category_id)');
        $this->addSql('ALTER TABLE trick ADD CONSTRAINT FK_D8F0A91EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE trick ADD CONSTRAINT FK_D8F0A91E12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('DROP INDEX uniq_8d93d649e7927c74 ON user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON user (email)');
    }
}
