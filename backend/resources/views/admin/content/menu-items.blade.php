{{-- Menu items (M15): each item saved or removed on its own; new items added below. --}}
@php($itemsUrl = url('/admin/api/menus/'.$record->getKey().'/items'))
<x-admin.card :title="__('admin.menus.items')">
    <div class="flex flex-col gap-3">
        @forelse ($panel['items'] as $item)
            <form class="flex flex-col gap-2 rounded-md border border-line-subtle p-3" data-module="form" data-action="{{ $itemsUrl.'/'.$item->id }}" data-method="PATCH" data-success="reload" novalidate>
                <div class="grid gap-2 sm:grid-cols-2">
                    <x-admin.field name="label" :id="'mi-'.$item->id.'-label'" :label="__('admin.fields.label')" :value="$item->label" maxlength="120" :disabled="! $canManage" />
                    <x-admin.field name="url" :id="'mi-'.$item->id.'-url'" :label="__('admin.fields.url')" :value="$item->url" maxlength="500" dir="ltr" data-nullable :disabled="! $canManage" />
                    <x-admin.field name="sort_order" type="number" :id="'mi-'.$item->id.'-sort'" :label="__('admin.fields.sort_order')" :value="$item->sort_order" :disabled="! $canManage" />
                    <label class="flex items-center gap-2 self-end text-sm"><input type="checkbox" name="is_active" class="size-4 accent-(--color-accent-text)" @checked($item->is_active) @disabled(! $canManage)> {{ __('admin.fields.is_active') }}</label>
                </div>
                <x-admin.form-alerts />
                @if ($canManage)
                    <div class="flex gap-2" data-module="action">
                        <button type="submit" class="admin-btn">{{ __('admin.common.save') }}</button>
                        <button type="button" class="admin-btn text-status-danger" data-action-url="{{ $itemsUrl.'/'.$item->id }}" data-action-method="DELETE" data-confirm-title="{{ __('admin.common.delete_confirm', ['name' => $item->label]) }}" data-confirm-danger>{{ __('admin.common.delete') }}</button>
                    </div>
                @endif
            </form>
        @empty
            <p class="text-sm text-content-muted">{{ __('admin.menus.empty') }}</p>
        @endforelse
    </div>
    @if ($canManage)
        <form class="mt-4 flex flex-col gap-2 border-t border-line-subtle pt-4" data-module="form" data-action="{{ $itemsUrl }}" data-method="POST" data-success="reload" novalidate>
            <p class="text-sm font-semibold">{{ __('admin.menus.add') }}</p>
            <x-admin.field name="label" id="mi-new-label" :label="__('admin.fields.label')" maxlength="120" />
            <x-admin.field name="url" id="mi-new-url" :label="__('admin.fields.url')" maxlength="500" dir="ltr" data-nullable :help="__('admin.help.safe_url')" />
            <x-admin.field name="sort_order" type="number" id="mi-new-sort" :label="__('admin.fields.sort_order')" value="0" />
            <input type="hidden" name="is_active" value="1">
            <x-admin.form-alerts />
            <div><button type="submit" class="admin-btn-primary">{{ __('admin.menus.add') }}</button></div>
        </form>
    @endif
</x-admin.card>
