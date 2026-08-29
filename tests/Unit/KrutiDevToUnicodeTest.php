<?php

namespace Tests\Unit;

use App\Support\KrutiDevToUnicode;
use PHPUnit\Framework\TestCase;

class KrutiDevToUnicodeTest extends TestCase
{
    public function test_it_detects_krutidev_text(): void
    {
        $legacy = 'vkt ge rqEgsa ,d cuekuql dk gky lqukrs gSaA lkeus tks rlohj gS] mlls rqEgsa ekywe gksxk fd cuekuql u rks iwjk canj gS] u iwjk vkneh A og vkneh vkSj cUnj ds chp esa ,d tkuoj gSA';

        $this->assertTrue(KrutiDevToUnicode::looksLike($legacy));
        $this->assertFalse(KrutiDevToUnicode::looksLike('This is a plain English story about a forest and the people who live there.'));
        $this->assertFalse(KrutiDevToUnicode::looksLike('आज हम तुम्हें एक कहानी सुनाते हैं।'));
    }

    public function test_it_converts_krutidev_story_paste_to_unicode(): void
    {
        $legacy = 'vkt ge rqEgsa ,d cuekuql dk gky lqukrs gSaA lkeus tks rlohj gS] mlls rqEgsa ekywe gksxk fd cuekuql u rks iwjk canj gS] u iwjk vkneh A og vkneh vkSj cUnj ds chp esa ,d tkuoj gSA exj og cM+k cyoku gksrk gS vkSj vknfe;ksa dks cM+h vklkuh ls ekj Mkyrk gSA og vf/kdrj vÝhdk ds taxy esa ik;k tkrk gSA';

        $unicode = KrutiDevToUnicode::convertIfNeeded($legacy);

        $this->assertStringContainsString('आज हम तुम्हें', $unicode);
        $this->assertStringContainsString('सुनाते हैं', $unicode);
        $this->assertStringContainsString('आदमी', $unicode);
        $this->assertStringContainsString('अफ्रीका', $unicode);
        $this->assertStringContainsString('जंगल में पाया जाता है', $unicode);
        $this->assertStringNotContainsString('rqEgsa', $unicode);
        $this->assertStringNotContainsString('gSaA', $unicode);
    }

    public function test_it_leaves_english_unchanged(): void
    {
        $english = 'The forest is home to many animals and they are not always easy to see.';

        $this->assertSame($english, KrutiDevToUnicode::convertIfNeeded($english));
    }
}
