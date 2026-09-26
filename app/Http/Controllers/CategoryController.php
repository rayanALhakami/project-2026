<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Categories', [
            'categories' => $request->user()
                ->categories()
                ->withCount('transactions')
                ->orderBy('type')
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $request->user()->categories()->create($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('تمت إضافة الفئة بنجاح.'),
        ]);

        return back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorizeCategory($request, $category);

        $category->update($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('تم تحديث الفئة بنجاح.'),
        ]);

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Category $category): RedirectResponse
    {
        $this->authorizeCategory($request, $category);

        $category->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('تم حذف الفئة بنجاح.'),
        ]);

        return back();
    }

    private function authorizeCategory(Request $request, Category $category): void
    {
        abort_unless($category->user_id === $request->user()->id, 404);
    }
}
