<?php

namespace Tests\Unit\Http\Controllers\Admin;

use App\Http\Controllers\Admin\TermsAndConditionController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class TermsAndConditionControllerTest extends TestCase
{
    #[Test]
    public function content_preview_decodes_html_entities_before_escaping_for_display(): void
    {
        $method = new ReflectionMethod(TermsAndConditionController::class, 'contentPreview');
        $controller = new TermsAndConditionController();

        $this->assertSame(
            'Shipping &amp; Handling &lt;script&gt;',
            $method->invoke($controller, '<p>Shipping &amp; Handling &lt;script&gt;</p>')
        );
    }
}
