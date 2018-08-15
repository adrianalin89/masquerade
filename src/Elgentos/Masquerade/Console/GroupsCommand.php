<?php

namespace Elgentos\Masquerade\Console;

use Elgentos\Masquerade\Helper\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GroupsCommand extends Command
{
    protected $config;
    protected $input;
    protected $output;
    protected $platformName;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'groups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List of groups (tables and columns) to be faked';

    /**
     * @var Config
     */
    protected $configHelper;

    protected function configure()
    {
        $this
            ->setName($this->name)
            ->setDescription($this->description)
            ->addOption('platform', null, InputOption::VALUE_REQUIRED);
    }

    /**
     * Execute the console command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $this->setup();

        $outputTable = new Table($output);
        $outputTable->setHeaders(['Platform', 'Group', 'Table', 'Column', 'Formatter']);

        $rows = [];

        foreach ($this->config as $groupName => $tables) {
            foreach ($tables as $tableName => $table) {
                $table['name'] = $tableName;
                foreach ($table['columns'] as $columnName => $column) {
                    $formatter = $column['formatter'];
                    if (is_array($formatter)) { $formatter = implode(', ', $formatter); }
                    $rows[] = [$this->platformName, $groupName, $tableName, $columnName, $formatter];
                }
            }
        }

        $outputTable->setRows($rows);
        $outputTable->render();
    }

    /**
     * @throws \Exception
     */
    private function setup()
    {
        $this->configHelper = new Config();
        if (file_exists('config.yaml')) {
            $databaseConfig = $this->configHelper->readYamlFile('.', 'config.yaml');
        }

        $this->platformName = $databaseConfig['platform'] ?? $this->input->getOption('platform');

        if (!$this->platformName) {
            throw new \Exception('No platformName set, use option --platform or set it in config.yaml');
        }

        $this->config = $this->configHelper->getConfig($this->platformName);
    }
}