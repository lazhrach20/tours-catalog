<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PhotoUploadRequest;
use App\Http\Requests\Admin\ReorderPhotosRequest;
use App\Http\Requests\Admin\SetPhotoCoverRequest;
use App\Models\Tour;
use App\Models\TourPhoto;
use App\Support\MediaUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminPhotoController extends Controller
{
    public function store(PhotoUploadRequest $request, Tour $tour): JsonResponse
    {
        $file = $request->file('photo');
        $path = $file->store('tours/'.$tour->id, 'public');
        $isCover = (bool) $request->boolean('is_cover');

        $maxSort = (int) $tour->photos()->max('sort_order');

        $photo = DB::transaction(function () use ($tour, $path, $request, $isCover, $maxSort) {
            if ($isCover) {
                $tour->photos()->update(['is_cover' => false]);
            }

            $hasCover = $tour->photos()->where('is_cover', true)->exists();
            $shouldBeCover = $isCover || ! $hasCover;

            return $tour->photos()->create([
                'path' => $path,
                'alt' => $request->string('alt')->toString() ?: null,
                'sort_order' => $maxSort + 1,
                'is_cover' => $shouldBeCover,
            ]);
        });

        return response()->json([
            'message' => 'Photo uploaded successfully.',
            'photo' => $this->photoPayload($photo),
        ], 201);
    }

    public function reorder(ReorderPhotosRequest $request, Tour $tour): JsonResponse
    {
        $photoIds = $request->validated('photo_ids');
        $photos = $tour->photos()->whereIn('id', $photoIds)->get()->keyBy('id');

        if ($photos->count() !== count($photoIds)) {
            return response()->json([
                'message' => 'One or more photos do not belong to this tour.',
                'code' => 'PHOTO_REORDER_INVALID',
            ], 422);
        }

        DB::transaction(function () use ($photoIds, $photos): void {
            foreach ($photoIds as $index => $photoId) {
                $photos[$photoId]->update(['sort_order' => $index]);
            }
        });

        $tour->load('photos');

        return response()->json([
            'message' => 'Photos reordered successfully.',
            'photos' => $tour->photos->map(fn (TourPhoto $photo) => $this->photoPayload($photo))->values(),
        ]);
    }

    public function setCover(SetPhotoCoverRequest $request, TourPhoto $photo): JsonResponse
    {
        $tour = $photo->tour;

        DB::transaction(function () use ($tour, $photo): void {
            $tour->photos()->update(['is_cover' => false]);
            $photo->update(['is_cover' => true]);
        });

        return response()->json([
            'message' => 'Cover photo updated successfully.',
            'photo' => $this->photoPayload($photo->fresh()),
        ]);
    }

    public function destroy(TourPhoto $photo): JsonResponse
    {
        $tour = $photo->tour;

        DB::transaction(function () use ($photo, $tour): void {
            $wasCover = $photo->is_cover;
            Storage::disk('public')->delete($photo->path);
            $photo->delete();

            if ($wasCover) {
                $nextCover = $tour->photos()->orderBy('sort_order')->first();
                if ($nextCover) {
                    $nextCover->update(['is_cover' => true]);
                }
            }
        });

        return response()->json([
            'message' => 'Photo deleted successfully.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function photoPayload(TourPhoto $photo): array
    {
        return [
            'id' => $photo->id,
            'path' => $photo->path,
            'url' => MediaUrl::publicPath($photo->path),
            'alt' => $photo->alt,
            'sort_order' => $photo->sort_order,
            'is_cover' => $photo->is_cover,
        ];
    }
}
