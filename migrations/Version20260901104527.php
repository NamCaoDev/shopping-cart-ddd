<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260901104527 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE users (id UUID NOT NULL, username VARCHAR(30) NOT NULL, email VARCHAR(320) NOT NULL, password VARCHAR(250) NOT NULL, roles JSON NOT NULL, is_active BOOLEAN DEFAULT false NOT NULL, is_verified BOOLEAN DEFAULT false NOT NULL, verification_token VARCHAR(255) DEFAULT NULL, is_google_auth_enabled BOOLEAN DEFAULT false NOT NULL, google_auth_secret VARCHAR(255) DEFAULT NULL, is_email_auth_enabled BOOLEAN DEFAULT false NOT NULL, email_auth_code VARCHAR(255) DEFAULT NULL, backup_codes VARCHAR(300) DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9F85E0677 ON users (username)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE users');
    }
}
