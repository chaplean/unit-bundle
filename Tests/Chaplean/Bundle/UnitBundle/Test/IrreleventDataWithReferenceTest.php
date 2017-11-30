<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Test;

use Chaplean\Bundle\UnitBundle\Entity\User;
use Chaplean\Bundle\UnitBundle\Test\LogicalTestCase;

/**
 * IrreleventDataWithReferenceTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.coop)
 * @since     2.0.0
 */
class IrreleventDataWithReferenceTest extends LogicalTestCase
{
    /**
     * @return void
     */
    public static function setUpBeforeClass()
    {
        self::markTestSkipped('');

        self::loadStaticFixtures(array(
            'Chaplean\Bundle\UnitBundle\DataFixtures\Liip\UserIrreleventWithReference\LoadUserData'
        ), true);
        
        parent::setUpBeforeClass();
    }

    /**
     * @return void
     */
    public function testGetUser()
    {
        $users = $this->em->getRepository('ChapleanUnitBundle:User')->findAll();

        $this->assertCount(7, $users);
    }

    /**
     * @return void
     */
    public function testGetActiveUser()
    {
        $status = self::$fixtures->getReference('status-active');

        /** @var User $user */
        $users = $this->em->getRepository('ChapleanUnitBundle:User')->findBy(array(
            'status' => $status
        ));

        $this->assertCount(3, $users);
    }

    /**
     * @return void
     */
    public function testGetInactiveUser()
    {
        $status = self::$fixtures->getReference('status-inactive');

        /** @var User $user */
        $users = $this->em->getRepository('ChapleanUnitBundle:User')->findBy(array(
            'status' => $status
        ));

        $this->assertCount(2, $users);
    }

    /**
     * @return void
     */
    public function testGetDeletedUser()
    {
        $status = self::$fixtures->getReference('status-deleted');

        /** @var User $user */
        $users = $this->em->getRepository('ChapleanUnitBundle:User')->findBy(array(
            'status' => $status
        ));

        $this->assertCount(2, $users);
    }
}
