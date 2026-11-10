<?php

namespace App\Http\Resources\Account;

use App\Domains\Cms\Enums\LinkTargetType;
use App\Domains\Cms\Services\LinkTargetResolver;
use App\Domains\Notifications\Enums\InboxNotificationKind;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Notifications\DatabaseNotification;

/**
 * An inbox notification (M-A5): title and body in the request locale, built from the stored kind
 * and parameters, and a structured link (`{type, value, url}`, as ADR-037; `order` and `ticket`
 * carry the public id). A row of an unknown kind is shown with its type and no text.
 *
 * @mixin DatabaseNotification
 */
class InboxNotificationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = is_array($this->data) ? $this->data : [];
        $kind = InboxNotificationKind::tryFrom((string) ($data['kind'] ?? $this->type));
        $params = array_map(strval(...), array_filter((array) ($data['params'] ?? []), is_scalar(...)));
        $linkType = LinkTargetType::tryFrom((string) ($data['link']['type'] ?? ''));
        $linkValue = $data['link']['value'] ?? null;

        return [
            'id' => $this->id,
            'type' => $kind?->value ?? $this->type,
            'category' => $kind?->category()->value,
            'title' => $kind === null ? null : __($kind->title(), $params),
            'body' => $kind === null ? null : __($kind->body(), $params),
            'link' => $linkType !== null && in_array($linkType, [LinkTargetType::Order, LinkTargetType::Ticket], true) && is_string($linkValue)
                ? app(LinkTargetResolver::class)->forAccount($linkType, $linkValue)->toArray()
                : null,
            'read_at' => $this->read_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
