<?php

namespace Magnifico\Phinx\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Loader;

class Migrate extends \Phinx\Console\Command\Migrate
{
	use \Magnifico\Phinx\BitrixAdapter;

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->addArgument('module', InputArgument::OPTIONAL | InputArgument::IS_ARRAY);
        parent::configure();
    }

    /**
     * @param string $module
     *
     * @return boolean
     */
    protected function checkMigrationsFolder(string $module) : bool
    {
        $path = Loader::getLocal('modules/'.$module) ?  Loader::getLocal('modules/'.$module) . '/migrations' : false;

        return $path && is_dir($path);
    }

    /**
     * @param array $modules
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function applyMigration(array $modules, InputInterface $input, OutputInterface $output)
    {
        foreach ($modules as $module) {
            if ($this->reconfigure($module, $input, $output)) {
                parent::execute($input, $output);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $inputModules = array_unique($input->getArgument('module'));
        $installedModules = array_pluck(ModuleManager::getInstalledModules(), "ID");

        if (empty($inputModules)) {
            $filteredModules = array_filter($installedModules, [$this, 'checkMigrationsFolder']);
            if (empty($filteredModules)) {
                return;
            }

            $this->applyMigration($filteredModules, $input, $output);
            return;
        }

        $filteredModules = [];
        foreach ($inputModules as $inputModule) {
            $foundedModulesByRegular = preg_filter(
                str_replace('*', '.*?', '/^' . addcslashes($inputModule, '.\+?[^]($)') . '$/i'),
                '$0',
                $installedModules
            );
            if (empty($foundedModulesByRegular)) {
                $output->writeln('<error>Not found any module by: "' . $inputModule . '"</error>');
                continue;
            }

            $checkedModules = array_filter($foundedModulesByRegular, [$this, 'checkMigrationsFolder']);
            if (empty($checkedModules)) {
                $output->writeln('<error>Not found any module(s) by "' . $inputModule . '".</error>');
                continue;
            }

            $filteredModules = array_merge($filteredModules, $checkedModules);
        }

        if (empty($filteredModules)) {
            return;
        }

        $this->applyMigration(array_unique($filteredModules), $input, $output);
    }
}
