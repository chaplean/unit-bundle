<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * SwiftMailerCacheUtility.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
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
     * @param Container $container
     */
    public function __construct(Container $container)
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
            $finder = Finder::create()->files()->in($this->swiftmailerCacheDir);

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
        $finder = Finder::create()->files()->in($this->swiftmailerCacheDir);

        if ($finder->count() === 0) {
            return null;
        }

        $messages = array();
        $messagesTimes = array();

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $message = unserialize($file->getContents());

            $messagesTimes[] = $message->getTime();
            $messages[] = $message;
        }

        array_multisort($messagesTimes, $messages);
        return array_values($messages);
    }
}
