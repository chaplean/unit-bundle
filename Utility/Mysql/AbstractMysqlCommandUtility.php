<?php

namespace Chaplean\Bundle\UnitBundle\Utility\Mysql;

use Doctrine\DBAL\Connection;

/**
 * Class AbstractMysqlCommandUtility.
 *
 * @package   Tests\Chaplean\Bundle\UnitBundle\Utility\Mysql
 * @author    Tom - Chaplean <tom@chaplean.coop>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.coop)
 * @since     4.2.0
 */
abstract class AbstractMysqlCommandUtility implements MysqlCommandUtilityInterface
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $file;

    /**
     * MysqlDumpCommandUtility constructor.
     *
     * @param Connection $connection
     * @param string     $file
     */
    public function __construct(Connection $connection, $file)
    {
        $this->connection = $connection;
        $this->file = $file;
    }

    /**
     * Execute the command and return the status of this one.
     *
     * @return integer
     */
    public function exec()
    {
        $output = null;
        $returnVar = null;

        ob_start();
        exec($this->getCommandLine() . ' 2> /dev/null', $output, $returnVar);
        ob_end_clean();

        return $returnVar;
    }

    /**
     * Command line arguments for mysql.
     *
     * @return string
     */
    public function getCommandLineArguments()
    {
        return '-h' . $this->connection->getHost() . ' -P' . $this->connection->getPort() . ' -u' . $this->connection->getUsername() . ' -p' . $this->connection->getPassword() .
               ' ' . $this->connection->getDatabase();
    }
}
