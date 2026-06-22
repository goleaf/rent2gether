<?php

namespace App\Livewire\Messages;

use App\Actions\Media\StoreOptimizedImageAction;
use App\Models\MessageThread;
use App\Services\Messaging\MessageService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class ChatWindow extends Component
{
    use WithFileUploads;

    #[Locked]
    public int $threadId;

    public string $body = '';

    public bool $important = false;

    public string $templateKey = '';

    /** @var array<int, mixed> */
    public array $uploads = [];

    public function mount(MessageThread $thread): void
    {
        abort_unless(auth()->check() && $thread->hasParticipant(auth()->user()), 403);

        $this->threadId = $thread->id;
        app(MessageService::class)->markThreadRead($thread, auth()->user());
    }

    public function applyTemplate(string $key): void
    {
        $templates = $this->quickTemplates();

        if (! array_key_exists($key, $templates)) {
            return;
        }

        $this->templateKey = $key;
        $this->body = $templates[$key];
    }

    public function send(StoreOptimizedImageAction $images): void
    {
        $validated = $this->validate($this->rules(), attributes: $this->validationAttributes());

        try {
            app(MessageService::class)->send(
                $this->thread(),
                auth()->user(),
                (string) ($validated['body'] ?? ''),
                $this->storedAttachments($images),
                $this->important,
            );
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $this->addError($field, $message);
                }
            }

            return;
        } catch (AuthorizationException) {
            abort(403);
        }

        $this->body = '';
        $this->important = false;
        $this->templateKey = '';
        $this->uploads = [];
        unset($this->threadMessages);
    }

    #[Computed]
    public function threadMessages()
    {
        return $this->thread()
            ->messages()
            ->select([
                'id',
                'thread_id',
                'sender_id',
                'sender_user_id',
                'recipient_user_id',
                'body',
                'attachments',
                'attachments_json',
                'is_system_message',
                'system_message',
                'is_important',
                'important',
                'read_at',
                'created_at',
            ])
            ->with(['sender:id,name'])
            ->orderBy('created_at')
            ->limit(100)
            ->get();
    }

    #[Computed]
    public function otherUser()
    {
        return $this->thread()->otherParticipant(auth()->user());
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function quickTemplates(): array
    {
        $role = (int) $this->thread()->guest_user_id === (int) auth()->id()
            ? 'guest'
            : 'host';

        $templates = app('translator')->get("messages.templates.{$role}");

        return is_array($templates) ? $templates : [];
    }

    public function render(): View
    {
        $thread = $this->thread();

        return view('livewire.messages.chat-window', [
            'thread' => $thread,
            'threadType' => $thread->type?->value ?? 'pre_booking',
            'placeTitle' => $this->placeTitle($thread),
            'otherUser' => $thread->otherParticipant(auth()->user()),
            'messageCards' => $this->messageCards(),
        ])->layout('layouts.app', [
            'title' => __('messages.thread.title'),
        ]);
    }

    /**
     * @return list<array{message:mixed,mine:bool,attachments:list<array<string,mixed>>}>
     */
    private function messageCards(): array
    {
        return $this->threadMessages
            ->map(fn ($message): array => [
                'message' => $message,
                'mine' => (int) ($message->sender_user_id ?: $message->sender_id) === (int) auth()->id(),
                'attachments' => $message->attachments ?: $message->attachments_json ?: [],
            ])
            ->values()
            ->all();
    }

    private function placeTitle(MessageThread $thread): ?string
    {
        return $thread->sleepingPlace?->translations?->firstWhere('locale', app()->getLocale())?->title
            ?: $thread->sleepingPlace?->translations?->firstWhere('locale', config('localization.fallback_locale', 'en'))?->title
            ?: $thread->sleepingPlace?->display_name;
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'body' => ['nullable', 'string', 'max:5000', 'required_without:uploads'],
            'important' => ['boolean'],
            'uploads' => ['array', 'max:3'],
            'uploads.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        $attributes = app('translator')->get('messages.validation_attributes');

        return is_array($attributes) ? $attributes : [];
    }

    private function thread(): MessageThread
    {
        return MessageThread::query()
            ->select([
                'id',
                'type',
                'guest_user_id',
                'host_user_id',
                'booking_id',
                'property_id',
                'sleeping_place_id',
                'last_message_at',
                'status',
            ])
            ->with([
                'guest:id,name',
                'host:id,name',
                'booking:id,reference,status,payment_status',
                'sleepingPlace:id,display_name',
                'sleepingPlace.translations:id,sleeping_place_id,locale,title',
            ])
            ->findOrFail($this->threadId);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function storedAttachments(StoreOptimizedImageAction $images): array
    {
        $attachments = [];

        foreach ($this->uploads as $upload) {
            $mime = $upload->getMimeType() ?: 'application/octet-stream';

            if (str_starts_with($mime, 'image/')) {
                $paths = $images->handle($upload, "messages/{$this->threadId}");

                $attachments[] = [
                    'path' => $paths['mobile_path'],
                    'thumbnail_path' => $paths['thumb_path'],
                    'full_path' => $paths['full_path'],
                    'original_name' => $upload->getClientOriginalName(),
                    'mime' => $paths['mime'] ?: 'image/webp',
                    'size' => $paths['size'],
                    'type' => 'image',
                ];

                continue;
            }

            $path = $upload->store("messages/{$this->threadId}", 'public');

            $attachments[] = [
                'path' => $path,
                'original_name' => $upload->getClientOriginalName(),
                'mime' => $mime,
                'size' => $upload->getSize(),
                'type' => str_starts_with($mime, 'image/') ? 'image' : 'document',
            ];
        }

        return $attachments;
    }
}
