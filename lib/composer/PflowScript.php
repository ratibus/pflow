<?php
use Composer\Script\Event;

class PflowScript
{
    /**
     * @static
     *
     * @param Composer\Script\Event $event
     *
     * @return int
     */
    public static function postInstall(Event $event) {
        $io = $event->getIO();
        $init = $io->askConfirmation('Do you want to start PFlow initialization ? ');

        if($init) {
            self::runPflow('init');
        }

        return 0;
    }

    /**
     * @static
     *
     * @param Composer\Script\Event $event
     *
     * @return int
     */
    public static function postUpdate(Event $event) {
        $io = $event->getIO();
        $init = $io->askConfirmation('Do you want to update PFlow ? ' . PHP_EOL . '<error>If you have uncommitted changes to your PFlow repository, the will be lost!</error> ');

        if($init) {
            self::runPflow('update');
        }

        return 0;
    }

    /**
     * @static
     *
     * @return string
     */
    private static function getBinary() {
        return getcwd() . '/bin/git-pflow';
    }

    /**
     * @static
     *
     * @param string $command
     *
     * @throws RuntimeException
     */
    private static function runPflow($command) {
        $pflow = self::getBinary();

        if(is_executable($pflow)) {
            passthru($pflow . ' ' . $command);
        } else {
            throw new RuntimeException(sprintf('PFlow binary (%s) is not executable', $pflow));
        }
    }
}
