<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Test;

use Chaplean\Bundle\MailerBundle\lib\classes\Chaplean\Message;
use Chaplean\Bundle\UnitBundle\Test\LogicalTestCase;

/**
 * MessageTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */
class MessageTest extends LogicalTestCase
{
    /**
     * @return void
     */
    public function testSendMail()
    {
        $message = new Message($this->getContainer()->getParameter('chaplean_mailer'));

        $result = $this->getContainer()->get('swiftmailer.mailer.default')->send($message);

        $this->assertEquals(1, $result);
        $this->assertInstanceOf(Message::class, $this->readMessages()[0]);
    }

    /**
     * @return void
     */
    public function testSendMultiMail()
    {
        $message = new Message($this->getContainer()->getParameter('chaplean_mailer'));
        $message->setTo('foo@bar.com');

        $result = $this->getContainer()->get('swiftmailer.mailer.default')->send($message);
        $result += $this->getContainer()->get('swiftmailer.mailer.default')->send($message);
        
        $this->assertEquals(2, $result);
        $this->assertCount(2, $this->readMessages());
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testGetInfoMessage()
    {
        $message = new Message($this->getContainer()->getParameter('chaplean_mailer'));
        $message->setTo('foo@bar.com');
        $message->setSubject('message test');
        $message->setBody('Chaplean is Awesome !!');

        $result = $this->getContainer()->get('swiftmailer.mailer.default')->send($message);

        $this->assertEquals(1, $result);

        /** @var Message $messageSended */
        $messageSended = $this->readMessages()[0];

        $this->assertTrue(array_key_exists('foo_bar_com@yopmail.com', $messageSended->getTo()));
        $this->assertEquals(array('staff@chaplean.com' => 'Chaplean'), $messageSended->getFrom());
        $this->assertEquals('[TEST] message test', $messageSended->getSubject());
        $this->assertEquals('Chaplean is Awesome !!', $messageSended->getBody());
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testReadWithoutMessage()
    {
        $message = $this->readMessages();

        $this->assertNull($message);
    }
}