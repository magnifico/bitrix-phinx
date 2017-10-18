<?php

namespace Magnifico\Phinx;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Phinx\Config\Config;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait BitrixAdapter
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->addArgument('module', InputArgument::REQUIRED);
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->reconfigure($input, $output)) {
            parent::execute($input, $output);
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return bool
     */
    protected function reconfigure(InputInterface $input, OutputInterface $output)
    {
        $module = $input->getArgument('module');

        if (false === ModuleManager::isModuleInstalled($module)) {
            $output->writeln('<error>Module "'.$module.'" is not installed.</error>');
            return false;
        }

        if (false === ($moduleRoot = Loader::getLocal('modules/'.$module))) {
            $output->writeln('<error>Failed to locate root directory for "'.$module.'" module.</error>');
            return false;
        }

        $connection = Application::getInstance()->getConnection();

        $connection = $connection->getConfiguration();

        $this->setConfig(new Config([
            'templates' => [
                'file' => dirname(__DIR__).'/migration.txt',
            ],
            'paths' => [
                'migrations' => $moduleRoot.'/migrations',
            ],
            'environments' => [
                'default_migration_table' => 'magnifico_phinx_migrations_of_'.str_replace('.', '_', $module),
                'default_database' => 'main',
                'main' => [
                    'adapter' => 'mysql',
                    'charset' => BX_UTF ? 'utf8' : 'cp1251',
                    'name' => $connection['database'],
                    'user' => $connection['login'],
                    'pass' => $connection['password'],
                    'host' => $connection['host'],
                ],
            ],
        ]));

        return true;
    }
}
