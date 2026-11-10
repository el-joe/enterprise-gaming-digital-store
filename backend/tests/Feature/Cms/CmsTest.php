<?php

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductImage;
use App\Domains\Cms\Models\HomepageSection;
use App\Domains\Cms\Models\Media;
use App\Domains\Cms\Models\Menu;
use App\Domains\Cms\Models\MenuItem;
use App\Domains\Seo\Models\Redirect;
use App\Domains\Shared\Services\HtmlSanitizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

describe('HTML sanitizer', function (): void {
    it('removes dangerous markup', function (string $input, string $expected): void {
        expect(app(HtmlSanitizer::class)->sanitize($input))->toBe($expected);
    })->with([
        'script' => ['<p>a</p><script>alert(1)</script>', '<p>a</p>'],
        'handler' => ['<p onclick="x()">a</p>', '<p>a</p>'],
        'js url' => ['<a href="javascript:alert(1)">a</a>', '<a>a</a>'],
        'obfuscated js url' => ['<a href="java&#10;script:alert(1)">a</a>', '<a>a</a>'],
        'data image' => ['<img src="data:text/html;base64,xx" alt="a">', '<img alt="a">'],
        'iframe' => ['<iframe src="https://x"></iframe>ok', 'ok'],
        'svg' => ['<svg onload="x()"><circle/></svg>ok', 'ok'],
        'unknown tag unwrapped' => ['<marquee>keep</marquee>', 'keep'],
        'style attribute' => ['<p style="position:fixed">a</p>', '<p>a</p>'],
    ]);

    it('keeps Arabic text and safe links', function (): void {
        $html = '<p dir="rtl">مرحبا</p><a href="https://example.test/x">y</a>';

        expect(app(HtmlSanitizer::class)->sanitize($html))->toBe($html);
    });

    it('adds noopener to a new-tab link', function (): void {
        expect(app(HtmlSanitizer::class)->sanitize('<a href="/x" target="_blank">y</a>'))
            ->toBe('<a href="/x" target="_blank" rel="noopener noreferrer">y</a>');
    });
});

describe('pages and posts', function (): void {
    it('sanitizes a page and stamps publication', function (): void {
        actingAsStaffWith(['content.pages']);

        $response = $this->postJson('/admin/api/pages', ['title' => 'T', 'slug' => 't', 'body' => '<script>x</script><p>y</p>', 'status' => 'published'])->assertCreated();

        expect($response->json('data.body'))->toBe('<p>y</p>')
            ->and($response->json('data.status'))->toBe('published')
            ->and($response->json('data.published_at'))->not->toBeNull();
    });

    it('clears the publication date when a page returns to draft', function (): void {
        actingAsStaffWith(['content.pages']);
        $id = $this->postJson('/admin/api/pages', ['title' => 'T', 'slug' => 't', 'body' => 'b', 'status' => 'published'])->json('data.id');

        $this->patchJson("/admin/api/pages/{$id}", ['status' => 'draft'])->assertOk()->assertJsonPath('data.published_at', null);
    });

    it('needs a future time to schedule a post', function (): void {
        actingAsStaffWith(['content.blog']);

        $this->postJson('/admin/api/blog-posts', ['title' => 'T', 'slug' => 't', 'body' => 'b', 'status' => 'scheduled', 'published_at' => now()->subDay()->toIso8601String()])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['published_at'], 'errors');

        $this->postJson('/admin/api/blog-posts', ['title' => 'T', 'slug' => 't', 'body' => 'b', 'status' => 'scheduled', 'published_at' => now()->addDay()->toIso8601String()])
            ->assertCreated();
    });
});

describe('menus', function (): void {
    beforeEach(function (): void {
        actingAsStaffWith(['content.menus']);
        $this->menu = Menu::factory()->create();
    });

    it('rejects a script URL', function (): void {
        $this->postJson("/admin/api/menus/{$this->menu->id}/items", ['label' => 'x', 'url' => 'javascript:alert(1)', 'is_active' => true])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['url'], 'errors');
    });

    it('rejects a link to a record that does not exist', function (): void {
        $this->postJson("/admin/api/menus/{$this->menu->id}/items", ['label' => 'x', 'linkable_type' => 'page', 'linkable_id' => 999999, 'is_active' => true])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['linkable_id'], 'errors');
    });

    it('needs exactly one of url or linked record', function (): void {
        $this->postJson("/admin/api/menus/{$this->menu->id}/items", ['label' => 'x', 'is_active' => true])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['url'], 'errors');
    });

    it('refuses a parent from another menu and a cycle', function (): void {
        $foreign = MenuItem::factory()->create();
        $parent = MenuItem::factory()->create(['menu_id' => $this->menu->id, 'url' => '/a']);
        $child = MenuItem::factory()->create(['menu_id' => $this->menu->id, 'parent_id' => $parent->id, 'url' => '/b']);

        $this->postJson("/admin/api/menus/{$this->menu->id}/items", ['label' => 'x', 'url' => '/x', 'is_active' => true, 'parent_id' => $foreign->id])
            ->assertUnprocessable()->assertJsonValidationErrors(['parent_id'], 'errors');

        $this->patchJson("/admin/api/menus/{$this->menu->id}/items/{$parent->id}", ['parent_id' => $child->id])
            ->assertUnprocessable()->assertJsonValidationErrors(['parent_id'], 'errors');
    });
});

it('replaces homepage section items in order and rejects unknown item types', function (): void {
    actingAsStaffWith(['content.homepage']);
    $section = HomepageSection::factory()->create();
    $product = Product::factory()->create();

    $this->putJson("/admin/api/homepage-sections/{$section->id}/items", ['items' => [['item_type' => 'product', 'item_id' => $product->id], ['item_type' => 'category', 'item_id' => $product->primary_category_id]]])
        ->assertOk()
        ->assertJsonPath('data.items.1.item_type', 'category')
        ->assertJsonPath('data.items.1.sort_order', 1);

    $this->putJson("/admin/api/homepage-sections/{$section->id}/items", ['items' => [['item_type' => 'user', 'item_id' => 1]]])
        ->assertUnprocessable();

    $this->putJson("/admin/api/homepage-sections/{$section->id}/items", ['items' => [['item_type' => 'product', 'item_id' => 999999]]])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['items.0.item_id'], 'errors');
});

it('validates banner urls and windows', function (): void {
    actingAsStaffWith(['marketing.banners']);
    $media = Media::factory()->create();

    $this->postJson('/admin/api/banners', [
        'placement' => 'hero', 'media_id' => $media->id, 'is_active' => true,
        'cta_url' => '//evil.example', 'starts_at' => '2026-02-01', 'ends_at' => '2026-01-01',
    ])->assertUnprocessable()->assertJsonValidationErrors(['cta_url', 'ends_at'], 'errors');
});

describe('redirects', function (): void {
    beforeEach(fn () => actingAsStaffWith(['seo.redirects']));

    it('rejects an off-site target, a self redirect and a chain', function (): void {
        Redirect::factory()->create(['source_path' => '/b', 'source_hash' => hash('sha256', '/b'), 'target_path' => '/c', 'is_active' => true]);

        $this->postJson('/admin/api/redirects', ['source_path' => '/a', 'target_path' => 'https://evil.example', 'status_code' => 301, 'is_active' => true])
            ->assertUnprocessable()->assertJsonValidationErrors(['target_path'], 'errors');
        $this->postJson('/admin/api/redirects', ['source_path' => '/a', 'target_path' => '/a/', 'status_code' => 301, 'is_active' => true])
            ->assertUnprocessable()->assertJsonValidationErrors(['target_path'], 'errors');
        $this->postJson('/admin/api/redirects', ['source_path' => '/a', 'target_path' => '/b', 'status_code' => 301, 'is_active' => true])
            ->assertUnprocessable()->assertJsonValidationErrors(['target_path'], 'errors');
    });

    it('stores a normalized hash and drops the target on 410', function (): void {
        $response = $this->postJson('/admin/api/redirects', ['source_path' => '/Old-Page/', 'target_path' => '/x', 'status_code' => 410, 'is_active' => true])->assertCreated();

        expect($response->json('data.source_path'))->toBe('/old-page')
            ->and($response->json('data.target_path'))->toBeNull();
        $this->assertDatabaseHas('redirects', ['source_hash' => hash('sha256', '/old-page')]);
    });

    it('returns a conflict for a duplicate source', function (): void {
        $payload = ['source_path' => '/dup', 'target_path' => '/x', 'status_code' => 301, 'is_active' => true];

        $this->postJson('/admin/api/redirects', $payload)->assertCreated();
        $this->postJson('/admin/api/redirects', $payload)->assertStatus(409);
    });
});

describe('media', function (): void {
    beforeEach(function (): void {
        Storage::fake('public');
        actingAsStaffWith(['content.media']);
    });

    it('re-encodes an upload to WebP under a random name', function (): void {
        $response = $this->postJson('/admin/api/media', ['file' => UploadedFile::fake()->image('../../evil.png', 40, 30), 'alt' => 'Hero'])->assertCreated();

        $path = $response->json('data.path');

        expect($path)->toEndWith('.webp')->not->toContain('evil')
            ->and($response->json('data.mime'))->toBe('image/webp')
            ->and($response->json('data.width'))->toBe(40);
        Storage::disk('public')->assertExists($path);

        $bytes = Storage::disk('public')->get($path);
        expect(substr($bytes, 0, 4))->toBe('RIFF')->and(substr($bytes, 8, 4))->toBe('WEBP');
    });

    it('rejects svg, non-images and oversized files', function (UploadedFile $file): void {
        $this->postJson('/admin/api/media', ['file' => $file])->assertUnprocessable()->assertJsonValidationErrors(['file'], 'errors');
    })->with([
        'svg' => fn () => UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
        'php' => fn () => UploadedFile::fake()->createWithContent('x.php', '<?php echo 1;'),
        'too large' => fn () => UploadedFile::fake()->create('x.png', 9000, 'image/png'),
    ]);

    it('rejects a php payload named like an image', function (): void {
        $this->postJson('/admin/api/media', ['file' => UploadedFile::fake()->createWithContent('x.png', '<?php echo 1;')])
            ->assertUnprocessable();
    });

    it('updates details but not the stored path', function (): void {
        $media = Media::factory()->create();

        $this->patchJson("/admin/api/media/{$media->public_id}", ['alt' => 'New', 'path' => '/etc/passwd'])
            ->assertOk()
            ->assertJsonPath('data.alt', 'New')
            ->assertJsonPath('data.path', $media->path);
    });

    it('refuses to delete media in use', function (): void {
        $image = ProductImage::factory()->create();

        $this->deleteJson("/admin/api/media/{$image->media->public_id}")->assertStatus(409);
    });

    it('needs content.media', function (): void {
        actingAsStaffWith([]);

        $this->postJson('/admin/api/media', ['file' => UploadedFile::fake()->image('a.png')])->assertForbidden();
    });
});
