<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240624142258 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE company ADD image_path1 VARCHAR(1024) DEFAULT NULL');
        $this->addSql('ALTER TABLE company ADD image_path2 VARCHAR(1024) DEFAULT NULL');
        $this->addSql('ALTER TABLE company ADD image_path3 VARCHAR(1024) DEFAULT NULL');
        $this->addSql('ALTER TABLE company ADD image_path4 VARCHAR(1024) DEFAULT NULL');
        $this->addSql('ALTER TABLE company ADD image_path5 VARCHAR(1024) DEFAULT NULL');
        $this->addSql('ALTER TABLE company ADD image_path6 VARCHAR(1024) DEFAULT NULL');
        $this->addSql('ALTER TABLE company ADD image_path7 VARCHAR(1024) DEFAULT NULL');
        $this->addSql('ALTER TABLE company ADD image_path8 VARCHAR(1024) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE company DROP image_path1');
        $this->addSql('ALTER TABLE company DROP image_path2');
        $this->addSql('ALTER TABLE company DROP image_path3');
        $this->addSql('ALTER TABLE company DROP image_path4');
        $this->addSql('ALTER TABLE company DROP image_path5');
        $this->addSql('ALTER TABLE company DROP image_path6');
        $this->addSql('ALTER TABLE company DROP image_path7');
        $this->addSql('ALTER TABLE company DROP image_path8');
    }
}
