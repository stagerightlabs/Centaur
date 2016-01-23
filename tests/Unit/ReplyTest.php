<?php

namespace Centaur\Tests\Integrated;

use Sentinel;
use Exception;
use Centaur\AuthManager;
use Centaur\Tests\TestCase;
use Centaur\Replies\FailureReply;
use Centaur\Replies\SuccessReply;
use Centaur\Replies\ExceptionReply;

class ReplyTest extends TestCase
{
    /** @var Centaur\AuthManager */
    protected $authManager;

    public function setUp()
    {
        parent::setUp();
        $this->authManager = $this->app->make(AuthManager::class);
    }

    /** @test */
    public function it_knows_when_it_is_successful()
    {
        $successReply = new SuccessReply('message');
        $failureReply = new FailureReply('message');
        $exceptionReply = new ExceptionReply('message');

        $this->assertTrue($successReply->isSuccessful());
        $this->assertFalse($failureReply->isSuccessful());
        $this->assertFalse($exceptionReply->isSuccessful());

        $this->assertFalse($successReply->isFailure());
        $this->assertTrue($failureReply->isFailure());
        $this->assertTrue($exceptionReply->isFailure());
    }

    /** @test */
    public function it_knows_when_it_has_a_message()
    {
        $reply = new SuccessReply;

        $this->assertFalse($reply->hasMessage());

        $reply->setMessage('This is a message');

        $this->assertTrue($reply->hasMessage());
        $this->assertEquals('This is a message', $reply->message);

        $reply->remove('message');

        $this->assertFalse($reply->hasMessage());
    }

    /** @test */
    public function it_can_manage_a_payload_of_arbitrary_data()
    {
        $user = Sentinel::findById(1);
        $reply = new SuccessReply('message', ['user' => $user]);

        $this->assertTrue($reply->hasPayload());
        $this->assertTrue($reply->has('user'));
        $this->assertInstanceOf('Cartalyst\Sentinel\Users\EloquentUser', $reply->user);

        $reply->clearPayload();

        $this->assertFalse($reply->hasPayload());
    }

    /** @test */
    public function it_can_remove_data_from_itself()
    {
        $user = Sentinel::findById(1);
        $reply = new SuccessReply('message', ['user' => 'fred']);

        $reply->remove('message');
        $reply->remove('user');

        $this->assertFalse($reply->hasMessage());
        $this->assertFalse($reply->hasPayload());
    }

    /** @test */
    public function it_delivers_exceptions()
    {
        $exception = new Exception;
        $reply = new ExceptionReply('message', [], $exception);

        $this->assertTrue($reply->has('exception'));
        $this->assertTrue($reply->caughtAnException());
        $this->assertInstanceOf('Exception', $reply->exception);

        $reply->remove('exception');

        $this->assertFalse($reply->has('exception'));

        $reply->setException($exception);

        $this->assertTrue($reply->has('exception'));
        $this->assertTrue($reply->caughtAnException());
    }

    /** @test  */
    public function it_can_be_cast_as_an_array()
    {
        $name = 'Andrei';
        $reply = new SuccessReply('This is a message.', ['name' => $name]);

        $expectation = [
            'status' => 200,
            'message' => 'This is a message.',
            'name' => 'Andrei',
        ];

        $this->assertEquals($expectation, $reply->toArray());

    }
}
