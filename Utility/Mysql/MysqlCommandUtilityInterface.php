<?php

namespace Chaplean\Bundle\UnitBundle\Utility\Mysql;

use Doctrine\DBAL\Connection;

interface MysqlCommandUtilityInterface
{
    /**
     * MysqlCommandUtilityInterface constructor.
     *
     * @param Connection $connection
     * @param string     $file
     */
    public function __construct(Connection $connection, $file);

    /**
     * Command line arguments for mysql.
     *
     * @return string
     */
    public function getCommandLineArguments();

    /**
     * Full command line to exec.
     *
     * @return string
     */
    public function getCommandLine();

    /**
     * Execute the command line
     *
     * @return string
     */
    public function exec();
}
