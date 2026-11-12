<?php

/**
 * Category "Storefront placement" (Storefront v2, Figma 117:11830): featured in header with a
 * header order, shown on the home page with a home position and an item limit (4 to 24, default
 * 12), edited on the category page, validated and audited.
 */

use App\Domains\Catalog\Models\Category;
use App\Domains\Cms\Models\HomepageSection;

it('defaults a new category to no placement and an item limit of 12', function (): void {
    adminWith(['catalog.manage']);

    $id = $this->postJson('/admin/api/categories', ['name' => 'Travel', 'slug' => 'travel', 'is_active' => true])->assertCreated()->json('data.id');

    expect(Category::query()->findOrFail($id)->only(['featured_in_header', 'header_order', 'show_on_home', 'home_position', 'home_limit']))
        ->toBe(['featured_in_header' => false, 'header_order' => 0, 'show_on_home' => false, 'home_position' => 0, 'home_limit' => 12]);
});

it('saves the placement fields and audits the change', function (): void {
    adminWith(['catalog.manage']);
    $category = Category::factory()->create(['slug' => 'shopping']);

    $this->patchJson("/admin/api/categories/{$category->id}", ['featured_in_header' => true, 'header_order' => 2, 'show_on_home' => true, 'home_position' => 1, 'home_limit' => 8])
        ->assertOk()
        ->assertJsonPath('data.featured_in_header', true)
        ->assertJsonPath('data.header_order', 2)
        ->assertJsonPath('data.show_on_home', true)
        ->assertJsonPath('data.home_position', 1)
        ->assertJsonPath('data.home_limit', 8);

    $this->assertDatabaseHas('audit_logs', ['action' => 'catalog.category.updated', 'subject_id' => $category->id]);
});

it('validates the placement fields', function (array $payload, string $field): void {
    adminWith(['catalog.manage']);
    $category = Category::factory()->create();

    $this->patchJson("/admin/api/categories/{$category->id}", $payload)->assertUnprocessable()->assertJsonValidationErrors($field);
})->with([
    'limit under 4' => [['home_limit' => 3], 'home_limit'],
    'limit over 24' => [['home_limit' => 25], 'home_limit'],
    'negative header order' => [['header_order' => -1], 'header_order'],
    'negative home position' => [['home_position' => -2], 'home_position'],
    'not a boolean' => [['featured_in_header' => 'often'], 'featured_in_header'],
]);

it('refuses placement changes without catalog.manage', function (): void {
    adminWith(['catalog.view']);
    $category = Category::factory()->create();

    $this->patchJson("/admin/api/categories/{$category->id}", ['show_on_home' => true])->assertForbidden();
});

it('shows the Storefront placement card with the computed See all and the home order', function (): void {
    adminWith(['catalog.view', 'catalog.manage']);
    HomepageSection::factory()->count(3)->create();
    Category::factory()->create(['name' => 'Entertainment', 'show_on_home' => true, 'home_position' => 2]);
    $shopping = Category::factory()->create(['name' => 'Shopping', 'slug' => 'shopping', 'show_on_home' => true, 'home_position' => 1, 'featured_in_header' => true]);

    $response = $this->get('/admin/catalog/categories/'.$shopping->getRouteKey())->assertOk()
        ->assertSee('Storefront placement')
        ->assertSee('Featured in header')
        ->assertSee('Header order')
        ->assertSee('Show on home page')
        ->assertSee('Home position')
        ->assertSee('Item limit')
        ->assertSee('1 of 8 header slots used.')
        ->assertSee('/c/shopping')
        ->assertSee('form="resource-form"', false)
        ->assertSee('← this category');

    $html = $response->getContent();
    expect($html)->toContain('>4</span> Shopping')->toContain('>5</span> Entertainment')
        ->and(strpos($html, '>4</span> Shopping'))->toBeLessThan(strpos($html, '>5</span> Entertainment'));
});
