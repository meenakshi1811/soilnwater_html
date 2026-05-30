<?php

namespace Tests\Unit\Http\Controllers\Admin;

use App\Http\Controllers\Admin\TermsAndConditionController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class TermsAndConditionControllerTest extends TestCase
{
    #[Test]
    public function content_preview_returns_decoded_plain_text_for_datatables(): void
    {
        $method = new ReflectionMethod(TermsAndConditionController::class, 'contentPreview');
        $controller = new TermsAndConditionController();
        $content = '<h1>SOILNWATER VENDOR TERMS &amp;amp; CONDITIONS</h1>'
            . '<p>Effective Date: 18 April, 2026 These Vendor Terms &amp; Conditions '
            . '(&amp;quot;Vendor Terms&amp;quot;) go here.</p>';

        $expected = 'SOILNWATER VENDOR TERMS & CONDITIONS Effective Date: 18 April, 2026 '
            . 'These Vendor Terms & Conditions ("Vendor Terms") ...';

        $this->assertSame($expected, $method->invoke($controller, $content));
    }
}
