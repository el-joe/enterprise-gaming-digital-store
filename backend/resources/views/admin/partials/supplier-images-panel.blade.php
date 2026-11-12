{{-- Supplier images panel of the platform and category edit pages (ResourceScreen::panel()). --}}
@include('admin.partials.supplier-images', ['candidates' => $panel['candidates'], 'activeMediaId' => $panel['active_media_id'], 'canManage' => $canManage])
