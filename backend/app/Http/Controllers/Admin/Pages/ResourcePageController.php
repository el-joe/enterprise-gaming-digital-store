<?php

namespace App\Http\Controllers\Admin\Pages;

use App\Domains\Catalog\Services\ContentTranslationAdminService;
use App\Domains\Seo\Services\SeoMetadataService;
use App\Http\Controllers\Controller;
use App\Http\Support\AdminScreens\ResourceScreen;
use App\Http\Support\AdminScreens\ResourceScreens;
use App\Http\Support\AdminTablePage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * List, create and edit pages of the declarative admin screens (M15,
 * App\Http\Support\AdminScreens). Each route names its screen in the route defaults and carries
 * the screen's permission; the forms post JSON to the admin API.
 */
class ResourcePageController extends Controller
{
    public function __construct(private ContentTranslationAdminService $translations, private SeoMetadataService $seo) {}

    public function index(Request $request, AdminTablePage $tables): View
    {
        $screen = $this->screen($request);

        if ($screen->indexView() !== null) {
            return view($screen->indexView(), ['screen' => $screen, ...$screen->indexData()]);
        }

        return view('admin.resource.index', [
            'screen' => $screen,
            'table' => $tables->make($screen::table(), $request->user()),
        ]);
    }

    public function create(Request $request): Response
    {
        $screen = $this->screen($request);

        return $this->form($screen, null);
    }

    public function edit(Request $request, string $record): Response
    {
        $screen = $this->screen($request);

        return $this->form($screen, $screen->find($record));
    }

    private function form(ResourceScreen $screen, ?Model $record): Response
    {
        return response()->view('admin.resource.form', [
            'screen' => $screen,
            'record' => $record,
            'groups' => $screen->fields($record),
            'canManage' => $screen->canManage(),
            'translationValues' => $record !== null && $screen->translations() !== null ? $this->translations->values($record) : [],
            'panel' => $record !== null && $screen->panel() !== null ? $screen->panelData($record) : [],
            'seoValues' => $record !== null && $screen->seoType() !== null ? $this->seo->values($record) : [],
            'aside' => $screen->asideData($record),
        ])->header('Cache-Control', 'no-store, private');
    }

    private function screen(Request $request): ResourceScreen
    {
        return ResourceScreens::make((string) $request->route()?->defaults['screen'], $request->user());
    }
}
