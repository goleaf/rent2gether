<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Messaging\MessageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_or_create_conversation(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $service = app(MessageService::class);
        $conv = $service->getOrCreateConversation($userA, $userB);

        $this->assertTrue($conv->hasParticipant($userA));
        $this->assertTrue($conv->hasParticipant($userB));

        $conv2 = $service->getOrCreateConversation($userA, $userB);
        $this->assertSame($conv->id, $conv2->id);
    }

    public function test_send_message(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $service = app(MessageService::class);
        $conv = $service->getOrCreateConversation($userA, $userB);
        $msg = $service->send($conv, $userA, 'Hello!');

        $this->assertSame('Hello!', $msg->body);
        $this->assertSame($userA->id, $msg->sender_id);
        $this->assertNull($msg->read_at);
    }

    public function test_mark_conversation_read(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $service = app(MessageService::class);
        $conv = $service->getOrCreateConversation($userA, $userB);
        $service->send($conv, $userA, 'Hey');

        $service->markConversationRead($conv, $userB);

        $this->assertNotNull($conv->messages->first()->fresh()->read_at);
    }
}
