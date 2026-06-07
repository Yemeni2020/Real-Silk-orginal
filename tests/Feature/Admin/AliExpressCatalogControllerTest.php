<?php

namespace Tests\Feature\Admin;

use App\Services\AliExpress\AliExpressCatalogBrowserService;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class AliExpressCatalogControllerTest extends TestCase
{
    use WithoutMiddleware;

    public function test_catalog_search_filter_validation(): void
    {
        $service = $this->getMockBuilder(AliExpressCatalogBrowserService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->app->instance(AliExpressCatalogBrowserService::class, $service);

        $response = $this->get(route('admin.aliexpress.catalog.search', [
            'keyword' => 'dress',
            'min_rating' => 9,
        ]));

        $response->assertSessionHasErrors('min_rating');
    }
}
