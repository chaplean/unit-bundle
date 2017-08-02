<?php

namespace Chaplean\Bundle\UnitBundle\Utility\Mysql;

/**
 * Class MysqlDumpCommandUtility.
 *
 * @package   Chaplean\Bundle\UnitBundle\Utility
 * @author    Tom - Chaplean <tom@chaplean.coop>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.coop)
 * @since     4.2.0
 */
class MysqlDumpCommandUtility extends AbstractMysqlCommandUtility
{
    /**
     * Full command line to exec.
     *
     * @return string
     */
    public function getCommandLine()
    {
        return 'mysqldump ' . $this->getCommandLineArguments() . ' > ' . $this->file;
    }
}
