<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Test;

use Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\User;

/**
 * AuthenticateTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2016 Chaplean (https://www.chaplean.coop)
 * @since     2.1.0
 */
class AuthenticateTest extends FunctionalTestCase
{
    /**
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::authenticate
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::getContainer
     *
     * @return void
     */
    public function testAuthenticate()
    {
        $user = new User('user', 'pwd');

        $this->authenticate($user);

        /** @var UsernamePasswordToken $token */
        $token = $this->getContainer()->get('security.token_storage')->getToken();

        $this->assertInstanceOf(User::class, $token->getUser());
        $this->assertEquals('user', $token->getUser()->getUsername());
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::createClient
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::authenticate
     *
     * @return void
     */
    public function testAuthenticateInClient()
    {
        $user = new User('user', 'pwd');

        $client = self::createClient();
        $this->authenticate($user, $client);

        /** @var UsernamePasswordToken $token */
        $token = $client->getContainer()->get('security.token_storage')->getToken();

        $this->assertInstanceOf(User::class, $token->getUser());
        $this->assertEquals('user', $token->getUser()->getUsername());

        $this->assertInstanceOf(
            UsernamePasswordToken::class,
            unserialize(
                $client->getContainer()->get('session')->get('_security_main')
            )
        );
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::createClient
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::tearDown
     *
     * @return void
     */
    public function testUnauthenticateOnTeardown()
    {
        $user = new User('user', 'pwd');

        $this->authenticate($user);
        $this->tearDown();

        /** @var UsernamePasswordToken $token */
        $token = $this->getContainer()->get('security.token_storage')->getToken();

        $this->assertNull($token);
    }
}
