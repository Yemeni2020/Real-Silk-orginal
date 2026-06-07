<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class AdminProductImportedViewSafetyTest extends TestCase
{
    public function test_choices_partial_handles_null_choice_options(): void
    {
        $html = view('admin-views.product.partials._choices', [
            'choice_options' => null,
            'choice_no' => null,
        ])->render();

        $this->assertIsString($html);
    }

    public function test_edit_sku_partial_handles_null_combinations(): void
    {
        $html = view('admin-views.product.partials._edit_sku_combinations', [
            'combinations' => null,
        ])->render();

        $this->assertIsString($html);
        $this->assertStringNotContainsString('<table', $html);
    }

    public function test_edit_view_color_logic_handles_null_colors(): void
    {
        $blade = <<<'BLADE'
@php($product = (object) ['colors' => null, 'images_full_url' => null, 'color_images_full_url' => null, 'color_image' => null])
@if(empty($product->colors) || !is_countable($product->colors) || count($product->colors) == 0)
    @foreach ((array) $product->images_full_url as $key => $photo)
        image
    @endforeach
@else
    @foreach ((array) $product->color_images_full_url as $photo)
        color
    @endforeach
@endif
<script>
let colors = {{ (!empty($product->colors) && is_countable($product->colors)) ? count($product->colors) : 0 }};
</script>
BLADE;

        $html = Blade::render($blade);

        $this->assertStringContainsString('let colors = 0', $html);
    }
}
