<?php

/**
 * M13 customer account pages (spec section 9) in English and Arabic: dashboard, orders, order
 * detail, digital codes, wallet, wishlist, reviews, tickets, notifications, preferences, profile
 * and security. Pages read through the existing services; every write goes through the JSON
 * account routes. Codes never appear in a page.
 */

use App\Domains\Catalog\Models\WishlistItem;
use App\Domains\Fulfillment\Jobs\FulfilOrder;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderEvent;
use App\Domains\Orders\Notifications\OrderDelivered;
use App\Domains\Reviews\Enums\ReviewStatus;
use App\Domains\Reviews\Models\Review;
use App\Domains\Support\Notifications\SupportTicketReplied;
use App\Domains\Wallet\Models\WalletTransaction;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

beforeEach(function (): void {
    storefrontWithoutBuild();
    Queue::fake([FulfilOrder::class]);
    $this->customer = User::factory()->create(['name' => 'Sara Ali', 'email' => 'sara@example.com', 'email_verified_at' => now()]);
});

dataset('account locales', [
    'en' => ['', 'Recent orders', 'Digital codes'],
    'ar' => ['/ar', 'أحدث الطلبات', 'الأكواد الرقمية'],
]);

describe('access', function (): void {
    it('sends guests to the sign-in page of the page language', function (string $path, string $login): void {
        $this->get($path)->assertRedirect(url($login));
    })->with([
        ['/account', '/login'],
        ['/account/orders', '/login'],
        ['/account/codes', '/login'],
        ['/account/security', '/login'],
        ['/ar/account/wallet', '/ar/login'],
        ['/ar/account/tickets', '/ar/login'],
    ]);

    it('serves every account page, never indexed, with the strict CSP and the account navigation', function (string $prefix): void {
        $ticket = accountPageTicket($this->customer);

        foreach (['', '/orders', '/codes', '/wallet', '/wishlist', '/reviews', '/tickets', '/tickets/new', '/tickets/'.$ticket->public_id, '/notifications', '/notifications/preferences', '/profile', '/security'] as $page) {
            $response = $this->actingAs($this->customer)->get($prefix.'/account'.$page)->assertOk()
                ->assertSee('<meta name="robots" content="noindex,nofollow">', false)
                ->assertSee('aria-current="page"', false)
                ->assertSee(url($prefix.'/account/codes'), false);

            assertCspSafeHtml($response);
        }
    })->with(['en' => [''], 'ar' => ['/ar']]);

    it('refuses a suspended account on the account pages', function (): void {
        $this->customer->forceFill(['status' => 'suspended'])->save();

        expect($this->actingAs($this->customer)->get('/account')->status())->toBeIn([302, 403]);
    });
});

describe('dashboard', function (): void {
    it('greets the customer with order, wallet and sealed-code figures, the sealed notice and recent orders', function (string $prefix, string $recent): void {
        [$order] = accountPageDeliveredOrder($this->customer);
        fundWallet($this->customer, '18.50');

        $this->actingAs($this->customer)->get($prefix.'/account')->assertOk()
            ->assertSee('Sara Ali')
            ->assertSee($recent)
            ->assertSee($order->order_number)
            ->assertSee(storefrontPrice('18.50'))
            ->assertSee('Steam Wallet $20')
            ->assertSee('data-module="logout"', false)
            ->assertDontSee(ACCOUNT_PAGE_CANARY_CODE);
    })->with('account locales');

    it('greets in English as the placeholder did', function (): void {
        $this->actingAs($this->customer)->get('/account')->assertOk()->assertSee('Welcome back, Sara Ali')->assertSee('Codes unrevealed');
    });

    it('shows an empty state and a verification banner for a new unverified customer', function (): void {
        $fresh = User::factory()->create(['email_verified_at' => null]);

        $this->actingAs($fresh)->get('/account')->assertOk()
            ->assertSee('No orders yet')
            ->assertSee('Your email address is not verified yet')
            ->assertSee('data-action-url="'.url('/email/verification-notification').'"', false);

        $this->actingAs($fresh)->get('/ar/account/orders')->assertOk()->assertSee('بريدك الإلكتروني غير مؤكد بعد');
    });

    it('shows no verification banner to a verified customer', function (): void {
        $this->actingAs($this->customer)->get('/account')->assertOk()->assertDontSee('data-verify-banner', false);
    });
});

describe('orders', function (): void {
    it('lists only the customer\'s orders with server-formatted totals and links to the detail', function (string $prefix): void {
        [$mine] = accountPageDeliveredOrder($this->customer);
        $other = paidOrderFor(storefrontVariant(storefrontProduct('Other Card')), orderAttributes: ['user_id' => User::factory()->create()->id]);

        $this->actingAs($this->customer)->get($prefix.'/account/orders')->assertOk()
            ->assertSee($mine->order_number)
            ->assertSee(url($prefix.'/account/orders/'.$mine->public_id), false)
            ->assertSee(storefrontPrice('20'))
            ->assertDontSee($other->order_number);
    })->with(['en' => [''], 'ar' => ['/ar']]);

    it('filters by status and period', function (): void {
        [$paid] = accountPageDeliveredOrder($this->customer);
        $old = paidOrderFor(storefrontVariant(storefrontProduct('Old Card')), orderAttributes: ['user_id' => $this->customer->id, 'created_at' => now()->subDays(100)], status: OrderStatus::Completed);

        $this->actingAs($this->customer)->get('/account/orders?status=completed')->assertOk()->assertSee($old->order_number)->assertDontSee($paid->order_number);
        $this->actingAs($this->customer)->get('/account/orders?period=90')->assertOk()->assertSee($paid->order_number)->assertDontSee($old->order_number);
        $this->actingAs($this->customer)->get('/account/orders?status=completed&period=30')->assertOk()->assertSee('No orders match these filters.');
    });

    it('sends an invalid filter back to the unfiltered page', function (): void {
        $this->actingAs($this->customer)->get('/account/orders?status=shipped')->assertRedirect(url('/account/orders'));
        $this->actingAs($this->customer)->get('/ar/account/codes?state=lost')->assertRedirect(url('/ar/account/codes'));
    });

    it('shows the detail with timeline, payment amounts, the reveal component and a refund request form, never the code', function (string $prefix): void {
        [$order, $unit] = accountPageDeliveredOrder($this->customer);
        $order->forceFill(['payment_total' => '20', 'payment_rate' => '1'])->save();
        OrderEvent::factory()->create(['order_id' => $order->id, 'from_status' => OrderStatus::PendingPayment, 'to_status' => OrderStatus::Paid]);

        $response = $this->actingAs($this->customer)->get($prefix.'/account/orders/'.$order->public_id)->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertSee($order->order_number)
            ->assertSee('data-module="reveal"', false)
            ->assertSee('data-reveal="'.$unit->public_id.'"', false)
            ->assertSee('data-reveal-url="'.url('/api/v1/account/orders/'.$order->public_id.'/units/__unit__/reveal').'"', false)
            ->assertSee('data-action="'.url('/api/v1/account/refund-requests').'"', false)
            ->assertSee('value="'.$order->public_id.'"', false)
            ->assertSee('•', false)
            ->assertSee(storefrontPrice('20'))
            ->assertDontSee(ACCOUNT_PAGE_CANARY_CODE);

        assertCspSafeHtml($response);
    })->with(['en' => [''], 'ar' => ['/ar']]);

    it('offers the payment retry on an unpaid order and no refund request', function (): void {
        $order = paidOrderFor(storefrontVariant(storefrontProduct()), orderAttributes: ['user_id' => $this->customer->id, 'paid_at' => null, 'primary_payment_id' => null], status: OrderStatus::PendingPayment);

        $this->actingAs($this->customer)->get('/account/orders/'.$order->public_id)->assertOk()
            ->assertSee('data-module="retry-payment"', false)
            ->assertSee('data-retry-url="'.url('/api/v1/checkout/orders/'.$order->public_id.'/retry-payment').'"', false)
            ->assertDontSee('data-action="'.url('/api/v1/account/refund-requests').'"', false);
    });

    it('shows a pending refund request instead of the form', function (): void {
        [$order] = accountPageDeliveredOrder($this->customer);
        $this->actingAs($this->customer)->postJson('/api/v1/account/refund-requests', ['order_public_id' => $order->public_id, 'reason' => 'The code did not arrive in time.'])->assertCreated();

        $this->actingAs($this->customer)->get('/account/orders/'.$order->public_id)->assertOk()
            ->assertSee('Your refund request is with our team.')
            ->assertDontSee('data-action="'.url('/api/v1/account/refund-requests').'"', false);
    });

    it('resolves the delivery email link to the account order page', function (): void {
        [$order] = accountPageDeliveredOrder($this->customer);

        $link = OrderDelivered::link($order);
        expect($link)->toBe(url('/account/orders/'.$order->public_id));

        $this->actingAs($this->customer)->get($link)->assertOk()->assertSee($order->order_number);
        $this->app['auth']->forgetGuards();
        $this->get($link)->assertRedirect(url('/login'));
    });
});

describe('digital codes', function (): void {
    it('lists delivered codes masked, with per-unit reveal URLs and no code in the page', function (string $prefix, string $recent, string $title): void {
        [$order, $unit] = accountPageDeliveredOrder($this->customer);

        $this->actingAs($this->customer)->get($prefix.'/account/codes')->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertSee($title)
            ->assertSee($order->order_number)
            ->assertSee('data-reveal-url="'.url('/api/v1/account/orders/'.$order->public_id.'/units/'.$unit->public_id.'/reveal').'"', false)
            ->assertSee('data-sealed="1"', false)
            ->assertDontSee(ACCOUNT_PAGE_CANARY_CODE);
    })->with('account locales');

    it('filters by state and searches by product or order number', function (): void {
        [$sealedOrder] = accountPageDeliveredOrder($this->customer, 'CANARY-AAAA-0000-0001', 'Roblox 800 Robux');
        [$revealedOrder, $revealed] = accountPageDeliveredOrder($this->customer, 'CANARY-BBBB-0000-0002', 'Xbox Gift Card');
        $revealed->forceFill(['first_revealed_at' => now()])->save();

        $this->actingAs($this->customer)->get('/account/codes?state=sealed')->assertOk()->assertSee($sealedOrder->order_number)->assertDontSee($revealedOrder->order_number);
        $this->actingAs($this->customer)->get('/account/codes?state=revealed')->assertOk()->assertSee($revealedOrder->order_number)->assertDontSee($sealedOrder->order_number);
        $this->actingAs($this->customer)->get('/account/codes?q=Robux')->assertOk()->assertSee($sealedOrder->order_number)->assertDontSee($revealedOrder->order_number);
        $this->actingAs($this->customer)->get('/account/codes?q='.$revealedOrder->order_number)->assertOk()->assertSee('Xbox Gift Card')->assertDontSee('Roblox 800 Robux');
    });
});

describe('wallet, wishlist and reviews', function (): void {
    it('shows the balance and the statement with server-formatted amounts', function (string $prefix): void {
        fundWallet($this->customer, '24.40');

        $this->actingAs($this->customer)->get($prefix.'/account/wallet')->assertOk()
            ->assertSee(storefrontPrice('24.40'))
            ->assertSee('+'.storefrontPrice('24.40'));

        expect(WalletTransaction::query()->count())->toBe(1);
    })->with(['en' => [''], 'ar' => ['/ar']]);

    it('shows saved products as cards and an empty state otherwise', function (string $prefix): void {
        $this->actingAs($this->customer)->get($prefix.'/account/wishlist')->assertOk()->assertDontSee('data-wishlist-card', false);

        $product = storefrontProduct('Roblox 800 Robux', '9.60');
        WishlistItem::query()->forceCreate(['user_id' => $this->customer->id, 'product_id' => $product->id]);

        $this->actingAs($this->customer)->get($prefix.'/account/wishlist')->assertOk()
            ->assertSee('Roblox 800 Robux')
            ->assertSee(storefrontPrice('9.60'))
            ->assertSee('data-slug="'.$product->slug.'"', false);
    })->with(['en' => [''], 'ar' => ['/ar']]);

    it('marks a saved product on the product page', function (): void {
        $product = storefrontProduct('Roblox 800 Robux', '9.60');
        WishlistItem::query()->forceCreate(['user_id' => $this->customer->id, 'product_id' => $product->id]);

        $this->actingAs($this->customer)->get('/products/'.$product->slug)->assertOk()->assertSee('data-module="wishlist"', false)->assertSee('aria-pressed="true"', false);
        $this->app['auth']->forgetGuards();
        $this->get('/products/'.$product->slug)->assertOk()->assertSee('aria-pressed="false"', false);
    });

    it('offers items to review and lists the customer\'s reviews with an edit form', function (string $prefix): void {
        [$order] = accountPageDeliveredOrder($this->customer);
        $item = $order->orderItems()->sole();
        [$reviewedOrder] = accountPageDeliveredOrder($this->customer, 'CANARY-REVW-0000-0003', 'Game Pass 1 Month');
        $reviewedItem = $reviewedOrder->orderItems()->sole();
        $review = Review::factory()->create(['user_id' => $this->customer->id, 'product_id' => $reviewedItem->product_id, 'order_item_id' => $reviewedItem->id, 'rating' => 4, 'title' => 'Fast delivery', 'status' => ReviewStatus::Approved]);

        $this->actingAs($this->customer)->get($prefix.'/account/reviews')->assertOk()
            ->assertSee('value="'.$item->id.'"', false)
            ->assertSee('Fast delivery')
            ->assertSee('data-action="'.url('/api/v1/account/reviews/'.$review->id).'"', false)
            ->assertSee('data-method="PATCH"', false);
    })->with(['en' => [''], 'ar' => ['/ar']]);
});

describe('support tickets', function (): void {
    it('lists tickets and shows a thread without internal notes, with the reply form', function (string $prefix): void {
        $ticket = accountPageTicket($this->customer);

        $this->actingAs($this->customer)->get($prefix.'/account/tickets')->assertOk()->assertSee('Code not working on my region');

        $this->actingAs($this->customer)->get($prefix.'/account/tickets/'.$ticket->public_id)->assertOk()
            ->assertSee('My code says invalid.')
            ->assertSee('We are checking with the supplier.')
            ->assertDontSee('INTERNAL-NOTE-SECRET')
            ->assertSee('data-action="'.url('/api/v1/account/tickets/'.$ticket->public_id.'/messages').'"', false);
    })->with(['en' => [''], 'ar' => ['/ar']]);

    it('opens the new-ticket form about one of the customer\'s orders only', function (): void {
        [$order] = accountPageDeliveredOrder($this->customer);
        $foreign = paidOrderFor(storefrontVariant(storefrontProduct()), orderAttributes: ['user_id' => User::factory()->create()->id]);

        $this->actingAs($this->customer)->get('/account/tickets/new?order='.$order->public_id)->assertOk()
            ->assertSee('name="order_public_id" value="'.$order->public_id.'"', false)
            ->assertSee('data-redirect="'.url('/account/tickets/__id__').'"', false);

        $this->actingAs($this->customer)->get('/account/tickets/new?order='.$foreign->public_id)->assertOk()
            ->assertDontSee('name="order_public_id"', false)
            ->assertDontSee($foreign->order_number);
    });

    it('resolves the ticket reply email link to the account ticket page', function (): void {
        $ticket = accountPageTicket($this->customer);
        $mail = (new SupportTicketReplied($ticket->id))->toMail($this->customer);

        expect($mail->actionUrl)->toBe(url('/account/tickets/'.$ticket->public_id));
        $this->actingAs($this->customer)->get($mail->actionUrl)->assertOk()->assertSee($ticket->subject);
    });
});

describe('notifications, profile and security', function (): void {
    it('shows the inbox with account links and the preference matrix', function (string $prefix): void {
        [$order] = accountPageDeliveredOrder($this->customer);
        $this->customer->notify(new OrderDelivered($order->id));

        $this->actingAs($this->customer)->get($prefix.'/account/notifications')->assertOk()
            ->assertSee(url($prefix.'/account/orders/'.$order->public_id), false)
            ->assertSee('data-action-url="'.url('/api/v1/account/notifications/read-all').'"', false);

        $this->actingAs($this->customer)->get($prefix.'/account/notifications/preferences')->assertOk()
            ->assertSee('data-action="'.url('/api/v1/account/notification-preferences').'"', false)
            ->assertSee('name="preferences[0][enabled]"', false)
            ->assertSee('checked disabled', false);
    })->with(['en' => [''], 'ar' => ['/ar']]);

    it('shows the profile with the saved country, language and display currency selected', function (string $prefix): void {
        storefrontProduct();
        $this->customer->forceFill(['country_code' => 'US', 'locale' => 'ar', 'display_currency' => 'USD'])->save();

        $this->actingAs($this->customer)->get($prefix.'/account/profile')->assertOk()
            ->assertSee('<option value="US" selected', false)
            ->assertSee('<option value="ar" selected', false)
            ->assertSee('<option value="USD" selected', false)
            ->assertSee('data-method="PATCH"', false)
            ->assertSee('sara@example.com');
    })->with(['en' => [''], 'ar' => ['/ar']]);

    it('shows password change, two-factor enrolment, the reveal history and this device', function (string $prefix): void {
        $this->actingAs($this->customer)->get($prefix.'/account/security')->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertSee('data-action="'.url('/api/v1/account/password').'"', false)
            ->assertSee('data-module="two-factor"', false)
            ->assertSee('data-2fa-start', false)
            ->assertSee('data-qr-url="'.url($prefix.'/account/security/two-factor/qr').'"', false)
            ->assertSee('data-action="'.url('/api/v1/account/devices/sign-out-others').'"', false);
    })->with(['en' => [''], 'ar' => ['/ar']]);

    it('serves the QR code of a pending enrolment only, as uncached SVG without the secret in any page', function (): void {
        $this->actingAs($this->customer)->get('/account/security/two-factor/qr')->assertNotFound();

        $secret = $this->actingAs($this->customer)->postJson('/api/v1/account/security/two-factor', ['current_password' => 'password'])->assertOk()->json('data.secret');

        $qr = $this->actingAs($this->customer)->get('/account/security/two-factor/qr')->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml')
            ->assertHeader('Cache-Control', 'no-store, private');

        expect($qr->getContent())->toStartWith('<svg')->not->toContain($secret);
        $this->actingAs($this->customer)->get('/account/security')->assertOk()->assertDontSee($secret);
    });

    it('offers turning two-factor off once it is on', function (): void {
        $this->customer->forceFill(['two_factor_secret' => 'JBSWY3DPEHPK3PXP', 'two_factor_confirmed_at' => now()])->save();

        $this->actingAs($this->customer)->get('/account/security')->assertOk()
            ->assertSee('data-2fa-disable', false)
            ->assertDontSee('data-2fa-start', false);
        $this->actingAs($this->customer)->get('/account/security/two-factor/qr')->assertNotFound();
    });

    it('lists the customer\'s reveal attempts, granted and denied', function (): void {
        [$order, $unit] = accountPageDeliveredOrder($this->customer);
        $this->actingAs($this->customer)->postJson("/api/v1/account/orders/{$order->public_id}/units/{$unit->public_id}/reveal")->assertStatus(423);
        $this->actingAs($this->customer)->withSession(['auth.password_confirmed_at' => now()->getTimestamp()])
            ->postJson("/api/v1/account/orders/{$order->public_id}/units/{$unit->public_id}/reveal")->assertOk();

        $this->actingAs($this->customer)->get('/account/security')->assertOk()
            ->assertSee('Steam Wallet $20')
            ->assertSee('Success')
            ->assertSee('Denied')
            ->assertDontSee(ACCOUNT_PAGE_CANARY_CODE);
    });
});

it('keeps the orders and ticket routes on ULIDs only', function (): void {
    $this->actingAs($this->customer)->get('/account/orders/12')->assertNotFound();
    $this->actingAs($this->customer)->get('/account/tickets/'.Str::uuid())->assertNotFound();
    expect(Order::query()->count())->toBe(0);
});

it('has the same account translation keys in English and Arabic', function (): void {
    $keys = static fn (string $locale): array => array_keys(Arr::dot(require lang_path("{$locale}/account.php")));

    expect($keys('ar'))->toEqualCanonicalizing($keys('en'));
});
