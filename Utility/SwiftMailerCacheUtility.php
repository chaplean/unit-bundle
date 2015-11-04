<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

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
     * @param string $swiftmailerCacheDir
     */
    public function __construct($swiftmailerCacheDir)
    {
        $this->swiftmailerCacheDir = $swiftmailerCacheDir;
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
     * @return mixed
     * @throws \Exception
     */
    public function readMessages()
    {
        $messages = array();
        $finder = Finder::create()->files()->in($this->swiftmailerCacheDir);

        if ($finder->count() == 0) {
            return null;
        }

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $messages[] = unserialize($file->getContents());
        }

        return count($messages) == 1 ? $messages[0] : $messages;
    }
}
