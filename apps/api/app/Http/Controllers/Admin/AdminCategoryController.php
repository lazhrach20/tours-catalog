<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\CategoryInUseException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Http\Resources\Admin\AdminCategoryResource;
use App\Models\Category;
use App\Support\SlugGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminCategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::query()->orderBy('name')->get();

        return AdminCategoryResource::collection($categories);
    }

    public function store(CategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = SlugGenerator::ensureUnique($data['slug'], Category::class);

        $category = Category::query()->create($data);

        return (new AdminCategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Category $category): AdminCategoryResource
    {
        return new AdminCategoryResource($category);
    }

    public function update(CategoryRequest $request, Category $category): AdminCategoryResource
    {
        $data = $request->validated();

        if (isset($data['slug'])) {
            $data['slug'] = SlugGenerator::ensureUnique($data['slug'], Category::class, $category->id);
        }

        $category->update($data);

        return new AdminCategoryResource($category);
    }

    public function destroy(Category $category): JsonResponse
    {
        if ($category->tours()->exists()) {
            throw new CategoryInUseException;
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully.',
        ]);
    }
}
