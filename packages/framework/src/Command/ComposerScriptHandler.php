<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Composer\Script\Event;

class ComposerScriptHandler
{
    /**
     * @param \Composer\Script\Event $event
     */
    public static function postInstall(Event $event)
    {
        static::executeCommands($event, [
            'shopsys:domains-urls:configure',
            'ckeditor:install --clear=skip --release=full --tag=4.5.11',
            'cache:clear --no-warmup',
        ]);
    }

    /**
     * @param \Composer\Script\Event $event
     */
    public static function postUpdate(Event $event)
    {
        static::executeCommands($event, [
            'shopsys:domains-urls:configure',
            'ckeditor:install --clear=skip --release=full --tag=4.5.11',
            'cache:clear --no-warmup',
        ]);
    }

    /**
     * @param \Composer\Script\Event $event
     * @param string[] $commands
     */
    protected static function executeCommands(Event $event, array $commands)
    {
        $io = $event->getIO();
        //$actionName = sprintf('execute Shopsys Framework commands for "%s" Composer event', $event->getName());

        //if ($consoleDir === null) {
        //    $io->writeError(sprintf('Could not locate console dir to %s.', $actionName));
        //    $io->writeError(sprintf('Commands "%s" not executed.', implode('", "', $commands)));
//
        //    return;
        //}

        foreach ($commands as $command) {
            $io->write(['', sprintf('> Running "%s" command:', $command)]);

            static::executeCommand($event, $consoleDir, $command, $options['process-timeout']);
        }
    }
}
