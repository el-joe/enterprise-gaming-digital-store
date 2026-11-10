<?php

namespace App\Domains\Notifications\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Enums\AuditLogActorType;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Customers\Enums\CustomerConsentChannel;
use App\Domains\Customers\Models\CustomerConsent;
use App\Domains\Identity\Models\User;
use App\Domains\Notifications\DTOs\PreferenceSetting;
use App\Domains\Notifications\Enums\NotificationPreferenceCategory as Category;
use App\Domains\Notifications\Enums\NotificationPreferenceChannel as Channel;
use App\Domains\Notifications\Exceptions\NotificationPreferenceMandatory;
use App\Domains\Notifications\Models\NotificationPreference;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Customer notification preferences, category x channel (M-A5, gap G6), over the existing
 * `notification_preferences` rows. A row overrides the default; without one:
 *
 * - `mail` and `database` (the account inbox) are on for orders, support and security;
 * - `sms` is off (opt-in, ADR-034, as `CustomerContactPolicy` reads it);
 * - marketing follows the customer's latest marketing consent for the channel (email marketing
 *   consent for mail and the inbox, SMS marketing consent for SMS), off without one.
 *
 * Mandatory, never switched off: order emails (the delivery link is how a customer reaches codes
 * and the failure notice announces a refund, ADR-009 and ADR-013) and security emails (password
 * reset and email verification are always sent by the framework). Guests have no preferences and
 * get email only.
 */
class NotificationPreferenceService
{
    public function __construct(private AuditLogger $audit) {}

    public static function isMandatory(Category $category, Channel $channel): bool
    {
        return $channel === Channel::Mail && in_array($category, [Category::Orders, Category::Security], true);
    }

    /**
     * The full matrix in a fixed order (categories, then channels, in enum order).
     *
     * @return list<PreferenceSetting>
     */
    public function settings(User $user): array
    {
        $rows = $this->rows($user);
        $settings = [];

        foreach (Category::cases() as $category) {
            foreach (Channel::cases() as $channel) {
                $settings[] = new PreferenceSetting($category, $channel, $this->effective($user, $rows, $category, $channel), self::isMandatory($category, $channel));
            }
        }

        return $settings;
    }

    /**
     * Save the given cells; a mandatory cell can be sent enabled but never disabled.
     *
     * @param  list<array{category: Category, channel: Channel, enabled: bool}>  $changes
     * @return list<PreferenceSetting>
     *
     * @throws NotificationPreferenceMandatory
     */
    public function update(User $user, array $changes): array
    {
        foreach ($changes as $index => $change) {
            if (! $change['enabled'] && self::isMandatory($change['category'], $change['channel'])) {
                throw new NotificationPreferenceMandatory("preferences.{$index}.enabled");
            }
        }

        DB::transaction(function () use ($user, $changes): void {
            foreach ($changes as $change) {
                $user->notificationPreferences()->updateOrCreate(
                    ['category' => $change['category'], 'channel' => $change['channel']],
                    ['enabled' => $change['enabled']],
                );
            }

            $this->audit->record(new AuditEntry(
                action: 'account.notification_preferences_updated',
                subject: $user,
                after: ['preferences' => array_map(static fn (array $change): array => ['category' => $change['category']->value, 'channel' => $change['channel']->value, 'enabled' => $change['enabled']], $changes)],
                actorType: AuditLogActorType::Customer,
                actorId: $user->id,
            ));
        });

        return $this->settings($user);
    }

    /**
     * The Laravel channels (`mail`, `database`) a notification of this category goes to. An
     * account follows its preferences; a guest (on-demand notifiable) gets email only.
     *
     * @param  list<Channel>  $supported
     * @return list<string>
     */
    public function channelsFor(object $notifiable, Category $category, array $supported): array
    {
        if (! $notifiable instanceof User) {
            return $notifiable instanceof AnonymousNotifiable && in_array(Channel::Mail, $supported, true) ? [Channel::Mail->value] : [];
        }

        $rows = $this->rows($notifiable, $category);

        return array_values(array_map(
            static fn (Channel $channel): string => $channel->value,
            array_filter($supported, fn (Channel $channel): bool => $this->effective($notifiable, $rows, $category, $channel)),
        ));
    }

    /**
     * @param  Collection<int, NotificationPreference>  $rows
     */
    private function effective(User $user, Collection $rows, Category $category, Channel $channel): bool
    {
        if (self::isMandatory($category, $channel)) {
            return true;
        }

        $row = $rows->first(static fn (NotificationPreference $row): bool => $row->category === $category && $row->channel === $channel);

        if ($row !== null) {
            return $row->enabled;
        }

        return match (true) {
            $channel === Channel::Sms && $category !== Category::Marketing => false,
            $category === Category::Marketing => $this->hasMarketingConsent($user, $channel === Channel::Sms ? CustomerConsentChannel::SmsMarketing : CustomerConsentChannel::EmailMarketing),
            default => true,
        };
    }

    /**
     * @return Collection<int, NotificationPreference>
     */
    private function rows(User $user, ?Category $category = null): Collection
    {
        return NotificationPreference::query()
            ->where('user_id', $user->id)
            ->when($category !== null, static fn ($rows) => $rows->where('category', $category))
            ->get();
    }

    private function hasMarketingConsent(User $user, CustomerConsentChannel $channel): bool
    {
        return (bool) CustomerConsent::query()
            ->where('user_id', $user->id)
            ->where('channel', $channel)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->value('granted');
    }
}
