<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231016085351 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pokemon (id INT AUTO_INCREMENT NOT NULL, pokedex_id INT NOT NULL, name VARCHAR(255) NOT NULL, total INT NOT NULL, hit_point INT NOT NULL, attack INT NOT NULL, defense INT NOT NULL, special_attack INT NOT NULL, special_defense INT NOT NULL, speed INT NOT NULL, generation INT NOT NULL, legendary TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pokemon_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pokemon_type_pokemon (pokemon_type_id INT NOT NULL, pokemon_id INT NOT NULL, INDEX IDX_B7A84DFA926F002 (pokemon_type_id), INDEX IDX_B7A84DF2FE71C3E (pokemon_id), PRIMARY KEY(pokemon_type_id, pokemon_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pokemon_type_pokemon ADD CONSTRAINT FK_B7A84DFA926F002 FOREIGN KEY (pokemon_type_id) REFERENCES pokemon_type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pokemon_type_pokemon ADD CONSTRAINT FK_B7A84DF2FE71C3E FOREIGN KEY (pokemon_id) REFERENCES pokemon (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pokemon_type_pokemon DROP FOREIGN KEY FK_B7A84DFA926F002');
        $this->addSql('ALTER TABLE pokemon_type_pokemon DROP FOREIGN KEY FK_B7A84DF2FE71C3E');
        $this->addSql('DROP TABLE pokemon');
        $this->addSql('DROP TABLE pokemon_type');
        $this->addSql('DROP TABLE pokemon_type_pokemon');
    }
}
