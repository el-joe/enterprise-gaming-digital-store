<?php

namespace App\Http\Controllers\Account;

use App\Domains\Reviews\Repositories\ReviewRepository;
use App\Domains\Reviews\Services\ReviewService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\PageRequest;
use App\Http\Requests\Account\StoreReviewRequest;
use App\Http\Requests\Account\UpdateReviewRequest;
use App\Http\Resources\Account\ReviewableItemResource;
use App\Http\Resources\Account\ReviewResource;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    public function __construct(private ReviewService $service, private ReviewRepository $reviews) {}

    public function index(PageRequest $request): JsonResponse
    {
        return $this->paginated($this->reviews->mine($request->user()->id, $request->page(), $request->perPage()), ReviewResource::class);
    }

    public function eligible(PageRequest $request): JsonResponse
    {
        return $this->success(['items' => ReviewableItemResource::collection($this->reviews->reviewableItems($request->user()->id))->resolve()]);
    }

    public function store(StoreReviewRequest $request): JsonResponse
    {
        return $this->success(['review' => ReviewResource::make($this->service->submit($request->user(), $request->validated()))->resolve()], 'Thank you. Your review will appear once it is approved.', status: 201);
    }

    /**
     * PATCH /reviews/{review} (M13): the customer's own review only; it returns to moderation.
     */
    public function update(UpdateReviewRequest $request, int $review): JsonResponse
    {
        return $this->success(['review' => ReviewResource::make($this->service->revise($request->user(), $review, $request->validated()))->resolve()], 'Thank you. Your changes will appear once they are approved.');
    }
}
