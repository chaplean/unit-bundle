<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\MailerBundle\lib\classes\Chaplean\Message;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * SwiftMailerCacheUtility.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.coop)
 * @since     2.0.0
 */
class SwiftMailerCacheUtility
{
    /**
     * @var string
     */
    private $swiftmailerCacheDir;

    /**
     * SwiftMailerCacheUtility constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        try {
            $this->swiftmailerCacheDir = $container->getParameter('swiftmailer.spool.default.file.path');
        } catch (\Exception $e) {
            $this->swiftmailerCacheDir = $container->getParameter('kernel.cache_dir') . '/switfmailer/';
        }
    }

    /**
     * @return void
     */
    public function cleanMailDir()
    {
        if (is_dir($this->swiftmailerCacheDir)) {
            $finder = Finder::create()
                ->files()
                ->in($this->swiftmailerCacheDir);

            /** @var SplFileInfo $file */
            foreach ($finder as $file) {
                unlink($file->getRealPath());
            }
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function readMessages()
    {
        $finder = Finder::create()
            ->files()
            ->in($this->swiftmailerCacheDir);

        if ($finder->count() === 0) {
            return null;
        }

        $timedMessages = array();
        $basicMessages = array();
        $messagesTimes = array();

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $message = unserialize($file->getContents());

            if ($message instanceof Message) {
                $messagesTimes[] = $message->getTime();
                $timedMessages[] = $message;
            } else {
                $basicMessages[] = $message;
            }
        }

        array_multisort($messagesTimes, $timedMessages);
        $messages = array_values($timedMessages);

        foreach ($basicMessages as $message) {
            $messages[] = $message;
        }

        return $messages;
    }
}
