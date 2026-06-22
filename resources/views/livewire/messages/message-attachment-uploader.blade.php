<div class="space-y-2">
    <flux:file-upload
        wire:model="uploads"
        multiple
        :label="__('messages.fields.attachment')"
        :description="__('messages.thread.attachments_helper')"
        :error="$errors->first('uploads')"
    >
        <flux:file-upload.dropzone
            :heading="__('messages.fields.attachment')"
            :text="__('messages.thread.attachments_helper')"
            with-progress
            inline
        />
    </flux:file-upload>
    <flux:error name="uploads.*" />
</div>
