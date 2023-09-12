<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230911093550 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE gift_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE gift_list_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "user_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE gift (id INT NOT NULL, gift_list_id INT DEFAULT NULL, nom VARCHAR(255) DEFAULT NULL, prix DOUBLE PRECISION DEFAULT NULL, image_name VARCHAR(255) DEFAULT NULL, lien_achat VARCHAR(800) DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, created_by INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A47C990D51F42524 ON gift (gift_list_id)');
        $this->addSql('COMMENT ON COLUMN gift.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN gift.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE gift_list (id INT NOT NULL, created_by_id INT NOT NULL, titre VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, theme VARCHAR(255) DEFAULT NULL, image_name VARCHAR(255) DEFAULT NULL, date_ouverture DATE NOT NULL, date_fermeture DATE NOT NULL, privacy BOOLEAN NOT NULL, password VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B6B50A45B03A8386 ON gift_list (created_by_id)');
        $this->addSql('COMMENT ON COLUMN gift_list.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN gift_list.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, is_verified BOOLEAN NOT NULL, nom VARCHAR(255) DEFAULT NULL, prenom VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('ALTER TABLE gift ADD CONSTRAINT FK_A47C990D51F42524 FOREIGN KEY (gift_list_id) REFERENCES gift_list (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE gift_list ADD CONSTRAINT FK_B6B50A45B03A8386 FOREIGN KEY (created_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE gift_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE gift_list_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE "user_id_seq" CASCADE');
        $this->addSql('ALTER TABLE gift DROP CONSTRAINT FK_A47C990D51F42524');
        $this->addSql('ALTER TABLE gift_list DROP CONSTRAINT FK_B6B50A45B03A8386');
        $this->addSql('DROP TABLE gift');
        $this->addSql('DROP TABLE gift_list');
        $this->addSql('DROP TABLE "user"');
    }
}
