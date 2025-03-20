<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('children')->whereNull('parent_id')->get();
        return Inertia::render('Categories/Index', [
            'categories' => $categories
        ]);
    }

    public function create()
    {
        return Inertia::render('Categories/Create', [
            'categories' => $this->getCategoriesHierarchy()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id'
        ]);

        Category::create($validated);

        return redirect()->route('categories.index')
            ->with('message', 'Kategori başarıyla oluşturuldu.');
    }

    public function edit(Category $category)
    {
        return Inertia::render('Categories/Edit', [
            'category' => $category,
            'categories' => $this->getCategoriesHierarchy($category->id)
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => [
                'nullable',
                'exists:categories,id',
                function ($attribute, $value, $fail) use ($category) {
                    if ($value === $category->id) {
                        $fail('Kategori kendisinin alt kategorisi olamaz.');
                    }
                }
            ]
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('message', 'Kategori başarıyla güncellendi.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('categories.index')
            ->with('message', 'Kategori başarıyla silindi.');
    }

    /**
     * Kategorileri hiyerarşik düzende getirir
     * 
     * @param int|null $excludeId Hariç tutulacak kategori ID'si (düzenleme sırasında)
     * @return array
     */
    private function getCategoriesHierarchy($excludeId = null)
    {
        $allCategories = Category::when($excludeId, function($query) use ($excludeId) {
            return $query->where('id', '!=', $excludeId);
        })->get();
        
        $rootCategories = $allCategories->whereNull('parent_id');
        
        return $rootCategories->map(function($category) use ($allCategories) {
            return $this->formatCategoryWithChildren($category, $allCategories);
        })->values()->all();
    }
    
    /**
     * Alt kategorileri de içeren formatlı kategori yapısını oluşturur
     * 
     * @param Category $category
     * @param \Illuminate\Support\Collection $allCategories
     * @param int $level
     * @return array
     */
    private function formatCategoryWithChildren($category, $allCategories, $level = 0)
    {
        $prefix = str_repeat('— ', $level);
        
        $formattedCategory = [
            'id' => $category->id,
            'name' => $prefix . $category->name,
            'original_name' => $category->name,
            'parent_id' => $category->parent_id,
            'level' => $level
        ];
        
        $children = $allCategories->where('parent_id', $category->id);
        
        if ($children->isNotEmpty()) {
            $formattedCategory['children'] = $children->map(function($child) use ($allCategories, $level) {
                return $this->formatCategoryWithChildren($child, $allCategories, $level + 1);
            })->values()->all();
        }
        
        return $formattedCategory;
    }
}