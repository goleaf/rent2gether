<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_items', function (Blueprint $table): void {
            $table->string('owner_type')->nullable()->after('id');
            $table->unsignedBigInteger('owner_id')->nullable()->after('owner_type');
            $table->string('thumb_path')->nullable()->after('thumbnail_path');
            $table->string('mobile_path')->nullable()->after('thumb_path');
            $table->string('full_path')->nullable()->after('mobile_path');
            $table->string('original_filename')->nullable()->after('full_path');
            $table->string('mime')->nullable()->after('mime_type');
            $table->unsignedInteger('size')->nullable()->after('size_bytes');
            $table->string('caption_en')->nullable()->after('alt_text');
            $table->string('caption_ru')->nullable()->after('caption_en');
            $table->boolean('is_primary')->default(false)->after('is_cover');

            $table->index(['owner_type', 'owner_id', 'collection', 'sort_order']);
            $table->index(['is_primary']);
        });
    }

    public function down(): void
    {
        Schema::table('media_items', function (Blueprint $table): void {
            $table->dropIndex(['owner_type', 'owner_id', 'collection', 'sort_order']);
            $table->dropIndex(['is_primary']);
            $table->dropColumn([
                'owner_type',
                'owner_id',
                'thumb_path',
                'mobile_path',
                'full_path',
                'original_filename',
                'mime',
                'size',
                'caption_en',
                'caption_ru',
                'is_primary',
            ]);
        });
    }
};
