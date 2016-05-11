<?php
namespace Tests\Chaplean\Bundle\UnitBundle;

use Chaplean\Bundle\UnitBundle\Test\LogicalTest;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\User;

/**
 * AuthenticateTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.com)
 * @since     2.
 */
class AuthenticateTest extends LogicalTest
{
    /**
     * @return void
     */
    public function testAuthenticate()
    {
        $user = new User('user', 'pwd');

        $this->authenticate($user);

        $this->assertInstanceOf(User::class, $this->getContainer()->get('security.token_storage')->getToken()->getUser());
        $this->assertEquals('user', $this->getContainer()->get('security.token_storage')->getToken()->getUser()->getUsername());
    }

    /**
     * @return void
     */
    public function testAuthenticateInClient()
    {
        $user = new User('user', 'pwd');

        $client = self::createClient();
        $this->authenticate($user, $client);

        $this->assertInstanceOf(User::class, $client->getContainer()->get('security.token_storage')->getToken()->getUser());
        $this->assertEquals('user', $client->getContainer()->get('security.token_storage')->getToken()->getUser()->getUsername());
        $this->assertInstanceOf(UsernamePasswordToken::class, unserialize($client->getContainer()->get('session')->get('_security_main')));
    }
}
