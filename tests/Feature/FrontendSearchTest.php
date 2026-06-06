<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FrontendSearchTest extends TestCase
{
    public static function searchModules(): array
    {
        return [
            'offers' => ['offers', 'frontend.offers.index'],
            'ads' => ['ads', 'frontend.ads.index'],
            'vendors' => ['vendors', 'frontend.vendors.index'],
            'consultants' => ['consultants', 'frontend.consultants.index'],
            'services' => ['services', 'frontend.service_providers.index'],
        ];
    }

    #[DataProvider('searchModules')]
    public function test_search_redirects_to_the_selected_module(string $module, string $routeName): void
    {
        $response = $this->get(route('frontend.search', [
            'module' => $module,
            'q' => '  irrigation  ',
        ]));

        $response->assertRedirect(route($routeName, ['search' => 'irrigation']));
    }

    public function test_unknown_search_module_falls_back_to_offers(): void
    {
        $response = $this->get(route('frontend.search', [
            'module' => 'unknown',
            'q' => 'compost',
        ]));

        $response->assertRedirect(route('frontend.offers.index', ['search' => 'compost']));
    }
}
