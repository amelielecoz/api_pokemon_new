<?php

namespace App\Command;

use App\Entity\Pokemon;
use App\Entity\PokemonType;
use App\Repository\PokemonRepository;
use App\Repository\PokemonTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-data',
    description: 'Command to import data in csv format into the database',
)]
class ImportDataCommand extends Command
{
    private string $filepath;

    const COLUMN_POKEDEX_ID = 0;
    const COLUMN_NAME = 1;
    const COLUMN_TYPES = [2, 3];
    const COLUMN_TOTAL = 4;
    const COLUMN_HIT_POINT = 5;
    const COLUMN_ATTACK = 6;
    const COLUMN_DEFENSE = 7;
    const COLUMN_SPECIAL_ATTACK = 8;
    const COLUMN_SPECIAL_DEFENSE = 9;
    const COLUMN_SPEED = 10;
    const COLUMN_GENERATION = 11;
    const COLUMN_LEGENDARY = 12;

    public function __construct(private readonly EntityManagerInterface $manager,
                                private PokemonRepository               $pokemonRepository,
                                private PokemonTypeRepository           $pokemonTypeRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('filepath', InputArgument::REQUIRED, 'Filepath of the .csv file to be imported')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->filepath = $input->getArgument('filepath');

        if ($this->filepath) {
            $io->note(sprintf('Import has just started from the file located at : %s', $this->filepath));

            $this->load($output);

            $io->note('Import is finished.');
        }

        $io->success('You have now imported all your Pokemons !');

        return Command::SUCCESS;
    }

    public function load(OutputInterface $output): void
    {
        $row = 1;
        if (($handle = fopen($this->filepath, "r")) !== false) {
            $flag = true;

            //keep in memory the Pokemon types
            $pokemonTypes = [];

            $fp = file($this->filepath, FILE_SKIP_EMPTY_LINES);
            $lines = count($fp) - 1;

            $progressBar = new ProgressBar($output, $lines);

            // starts and displays the progress bar
            $progressBar->start();
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if ($flag) {
                    $flag = false;
                    continue;
                } //allows to skip headers (first row)
                $row++;
                $progressBar->advance();

                $this->loadPokemonTypes($data, $pokemonTypes);

                $this->loadPokemons($data);
            }

            fclose($handle);
            $progressBar->finish();
        }
    }

    public function loadPokemons($data): void
    {
        $pokemon = new Pokemon();

        $pokemon->setPokedexId(intval($data[self::COLUMN_POKEDEX_ID]));
        $pokemon->setName($data[self::COLUMN_NAME]);

        foreach (self::COLUMN_TYPES as $COLUMN_TYPE) {
            if ($data[$COLUMN_TYPE]) {
                $pokemon->addType($this->pokemonTypeRepository->findOneBy(['name' => $data[$COLUMN_TYPE]]));
            }
        }

        $pokemon->setTotal(intval($data[self::COLUMN_TOTAL]));
        $pokemon->setHitPoint(intval($data[self::COLUMN_HIT_POINT]));
        $pokemon->setAttack(intval($data[self::COLUMN_ATTACK]));
        $pokemon->setDefense(intval($data[self::COLUMN_DEFENSE]));
        $pokemon->setSpecialAttack(intval($data[self::COLUMN_SPECIAL_ATTACK]));
        $pokemon->setSpecialDefense(intval($data[self::COLUMN_SPECIAL_DEFENSE]));
        $pokemon->setSpeed(intval($data[self::COLUMN_SPEED]));
        $pokemon->setGeneration(intval($data[self::COLUMN_GENERATION]));
        $pokemon->setLegendary($data[self::COLUMN_LEGENDARY]);
        $this->manager->persist($pokemon);
        $this->manager->flush();
    }

    /**
     * @param mixed $pokemonType
     * @return void
     */
    public function savePokemonType(mixed $pokemonType): void
    {
        $type1 = new PokemonType();
        $type1->setName($pokemonType);
        $this->manager->persist($type1);

        $this->manager->flush();
    }

    /**
     * @param array $data
     * @param array $pokemonTypes
     * @return void
     */
    public function loadPokemonTypes(array $data, array $pokemonTypes): void
    {
        foreach (self::COLUMN_TYPES as $COLUMN_TYPE) {
            if (!empty($data[$COLUMN_TYPE])) {
                $pokemonType = $data[$COLUMN_TYPE];

                if (!in_array($pokemonType, $pokemonTypes)) {
                    $pokemonTypes[] = $pokemonType;
                    $this->savePokemonType($pokemonType);
                }
            }
        }
    }
}