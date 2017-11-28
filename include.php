<?php

require_once __DIR__.'/tools.php';

$eventManager = \Bitrix\Main\EventManager::getInstance();

$eventManager->addEventHandler('magnifico.console', 'OnBeforeRun', function(\Bitrix\Main\Event $event){
    $app = $event->getParameter('app');

    $commands = [
        'phinx:create' => new \Magnifico\Phinx\Command\Create(),
        'phinx:migrate' => new \Magnifico\Phinx\Command\Migrate(),
        'phinx:rollback' => new \Magnifico\Phinx\Command\Rollback(),
        'phinx:status' => new \Magnifico\Phinx\Command\Status(),
    ];

    foreach ($commands as $name => $command) {
        $command->setName($name);
        $app->add($command);
    }
});
