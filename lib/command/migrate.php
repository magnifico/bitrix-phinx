<?php

namespace Magnifico\Phinx\Command;

use Bitrix\Main\Config;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
    protected function checkMigrationsFolder($module)
    {
        $path = Loader::getLocal('modules/'.$module) ?  Loader::getLocal('modules/'.$module) . '/migrations' : false;

        return $path && is_dir($path);
    }

    /**
     * @param array $modules
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function applyMigration($modules, InputInterface $input, OutputInterface $output)
    {
        if (1 === count($modules)) {
            if ($this->reconfigure(current($modules), $input, $output)) {
                parent::execute($input, $output);
            }

            return;
        }

        foreach ($modules as $module) {
            $this->runNewProcess($module, $input, $output);
        }
    }

    /**
     * @param string $module
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function runNewProcess($module, InputInterface $input, OutputInterface $output)
    {
        $env = array(
            'PHP_BIN' => Config\Option::get('magnifico.phinx', 'php_bin', PHP_BINARY),
            'MANAGER_FILE' => Config\Option::get('magnifico.phinx', 'manager_file', realpath($_SERVER['argv'][0])),
        );

        if (!is_executable($env['PHP_BIN'])) {
            throw new \Exception('Incorect php bin');
        }

        if (!is_file($env['MANAGER_FILE'])) {
            throw new \Exception('Incorect manager file');
        }

        $descriptors = array(
            1 => STDOUT,
            2 => STDERR,
        );

        $process = proc_open(
            sprintf('%s %s phinx:migrate %s', $env['PHP_BIN'], $env['MANAGER_FILE'], $module),
            $descriptors,
            $pipes,
            realpath($_SERVER['DOCUMENT_ROOT'].'/../'),
            $env
        );

        if (false === $process) {
            throw new \Exception('Fail run migration process');
        }

        if (0 !== proc_close($process)) {
            throw new \Exception('Something went wrong');
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
