<?php

namespace App\Http\Controllers\Web\Account;

use App\Domains\Reviews\Repositories\ReviewRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Account\AccountPageRequest;
use App\Http\Resources\Account\ReviewableItemResource;
use App\Http\Resources\Account\ReviewResource;
use Illuminate\View\View;

/**
 * Reviews (`/account/reviews`, M13): items waiting for a review (write) and the customer's own
 * reviews with their moderation status (edit). Writes go to `POST` and `PATCH /api/v1/account/reviews`.
 */
class ReviewController extends Controller
{
    public function index(AccountPageRequest $request, ReviewRepository $reviews): View
    {
        $page = $reviews->mine($request->user()->id, $request->page(), AccountPageRequest::PER_PAGE)->withPath($request->url());

        return view('account.reviews', [
            'eligible' => ReviewableItemResource::collection($reviews->reviewableItems($request->user()->id))->resolve($request),
            'reviews' => ReviewResource::collection($page->getCollection())->resolve($request),
            'paginator' => $page,
            'verified' => $request->user()->hasVerifiedEmail(),
        ]);
    }
}
