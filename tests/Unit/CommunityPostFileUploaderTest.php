<?php

namespace Tests\Unit;

use App\Support\CommunityPostFileUploader;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CommunityPostFileUploaderTest extends TestCase
{
    protected function tearDown(): void
    {
        foreach (glob(public_path('uploads/community-posts/issues/*')) ?: [] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        parent::tearDown();
    }

    public function test_attachment_is_stored_under_public_uploads_directory(): void
    {
        $file = UploadedFile::fake()->create('field-report.pdf', 32, 'application/pdf');

        $attachment = CommunityPostFileUploader::storeAttachment($file);

        $this->assertStringStartsWith('uploads/community-posts/issues/', $attachment['path']);
        $this->assertFileExists(public_path($attachment['path']));
        $this->assertStringContainsString('/uploads/community-posts/issues/', $attachment['url']);
    }
}
