<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260419181259 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE gifts (id INT AUTO_INCREMENT NOT NULL, gift_name VARCHAR(255) NOT NULL, point_cost INT NOT NULL, stock INT DEFAULT 0 NOT NULL, status VARCHAR(50) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE points (id INT AUTO_INCREMENT NOT NULL, point_amount INT NOT NULL, description VARCHAR(500) DEFAULT NULL, created_at DATETIME NOT NULL, wallet_id INT NOT NULL, transaction_id INT DEFAULT NULL, redemption_id INT DEFAULT NULL, INDEX IDX_27BA8E29712520F3 (wallet_id), UNIQUE INDEX UNIQ_27BA8E292FC0CB0F (transaction_id), INDEX IDX_27BA8E29DDD59F9C (redemption_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE redemptions (id INT AUTO_INCREMENT NOT NULL, points_used INT NOT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, member_id INT NOT NULL, gift_id INT NOT NULL, INDEX IDX_B945DF4D7597D3FE (member_id), INDEX IDX_B945DF4D97A95A83 (gift_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE transactions (id INT AUTO_INCREMENT NOT NULL, amount NUMERIC(15, 2) NOT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, member_id INT NOT NULL, INDEX IDX_EAA81A4C7597D3FE (member_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE points ADD CONSTRAINT FK_27BA8E29712520F3 FOREIGN KEY (wallet_id) REFERENCES wallets (id)');
        $this->addSql('ALTER TABLE points ADD CONSTRAINT FK_27BA8E292FC0CB0F FOREIGN KEY (transaction_id) REFERENCES transactions (id)');
        $this->addSql('ALTER TABLE points ADD CONSTRAINT FK_27BA8E29DDD59F9C FOREIGN KEY (redemption_id) REFERENCES redemptions (id)');
        $this->addSql('ALTER TABLE redemptions ADD CONSTRAINT FK_B945DF4D7597D3FE FOREIGN KEY (member_id) REFERENCES members (id)');
        $this->addSql('ALTER TABLE redemptions ADD CONSTRAINT FK_B945DF4D97A95A83 FOREIGN KEY (gift_id) REFERENCES gifts (id)');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4C7597D3FE FOREIGN KEY (member_id) REFERENCES members (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_45A0D2FFE7927C74 ON members (email)');
        $this->addSql('ALTER TABLE wallets CHANGE balance balance NUMERIC(15, 2) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE wallets ADD CONSTRAINT FK_967AAA6C7597D3FE FOREIGN KEY (member_id) REFERENCES members (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_967AAA6C7597D3FE ON wallets (member_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE points DROP FOREIGN KEY FK_27BA8E29712520F3');
        $this->addSql('ALTER TABLE points DROP FOREIGN KEY FK_27BA8E292FC0CB0F');
        $this->addSql('ALTER TABLE points DROP FOREIGN KEY FK_27BA8E29DDD59F9C');
        $this->addSql('ALTER TABLE redemptions DROP FOREIGN KEY FK_B945DF4D7597D3FE');
        $this->addSql('ALTER TABLE redemptions DROP FOREIGN KEY FK_B945DF4D97A95A83');
        $this->addSql('ALTER TABLE transactions DROP FOREIGN KEY FK_EAA81A4C7597D3FE');
        $this->addSql('DROP TABLE gifts');
        $this->addSql('DROP TABLE points');
        $this->addSql('DROP TABLE redemptions');
        $this->addSql('DROP TABLE transactions');
        $this->addSql('DROP INDEX UNIQ_45A0D2FFE7927C74 ON members');
        $this->addSql('ALTER TABLE wallets DROP FOREIGN KEY FK_967AAA6C7597D3FE');
        $this->addSql('DROP INDEX UNIQ_967AAA6C7597D3FE ON wallets');
        $this->addSql('ALTER TABLE wallets CHANGE balance balance INT NOT NULL');
    }
}
