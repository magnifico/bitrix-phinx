<?php

$eventManager = \Bitrix\Main\EventManager::getInstance();

$eventManager->addEventHandler('magnifico.console', 'OnBeforeRun', function(\Bitrix\Main\Event $event){
    $app = $event->getParameter('app');

    $commands = [
        'phinx:create' => new class() extends \Phinx\Console\Command\Create {
            use \Magnifico\Phinx\BitrixAdapter;
        },
        'phinx:migrate' => new class() extends \Phinx\Console\Command\Migrate {
            use \Magnifico\Phinx\BitrixAdapter;
        },
        'phinx:rollback' => new class() extends \Phinx\Console\Command\Rollback {
            use \Magnifico\Phinx\BitrixAdapter;
        },
        'phinx:status' => new class() extends \Phinx\Console\Command\Status {
            use \Magnifico\Phinx\BitrixAdapter;
        },
    ];

    foreach ($commands as $name => $command) {
        $command->setName($name);
        $app->add($command);
    }
});
