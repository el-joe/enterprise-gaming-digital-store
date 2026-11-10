<?php

namespace App\Http\Controllers\Admin\Reviews;

use App\Domains\Reviews\Models\Review;
use App\Domains\Reviews\Repositories\ReviewRepository;
use App\Domains\Reviews\Services\ReviewService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\Reviews\FeatureReviewRequest;
use App\Http\Requests\Admin\Reviews\ModerateReviewRequest;
use App\Http\Resources\Admin\Reviews\ReviewResource;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    public function __construct(private ReviewService $service) {}

    public function index(AdminListRequest $request, ReviewRepository $reviews): JsonResponse
    {
        return $this->paginated($reviews->adminPage($request->toListQuery()), ReviewResource::class);
    }

    public function show(Review $review): JsonResponse
    {
        return $this->success(ReviewResource::make($review->load(['product:id,name,slug', 'user:id,name,email']))->resolve());
    }

    public function approve(ModerateReviewRequest $request, Review $review): JsonResponse
    {
        return $this->success(ReviewResource::make($this->service->approve($review, $request->user()))->resolve(), 'Approved.');
    }

    public function reject(ModerateReviewRequest $request, Review $review): JsonResponse
    {
        return $this->success(ReviewResource::make($this->service->reject($review, $request->user(), $request->validated('reason')))->resolve(), 'Rejected.');
    }

    public function feature(FeatureReviewRequest $request, Review $review): JsonResponse
    {
        return $this->success(ReviewResource::make($this->service->feature($review, $request->boolean('is_featured'), $request->user()))->resolve(), 'Saved.');
    }

    public function destroy(Review $review): JsonResponse
    {
        $this->service->delete($review);

        return $this->success(message: 'Deleted.');
    }
}
