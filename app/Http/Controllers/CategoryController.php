<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    public function index()
    {
        $categories = Category::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50'],
        ]);

        $exists = Category::where('user_id', auth()->id())
            ->where('name', $data['name'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'You already have this category.'])->withInput();
        }

        Category::create([
            'user_id' => auth()->id(),
            'name' => $data['name'],
        ]);

        return redirect()->route('categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $category)
    {
        abort_unless($category->user_id === auth()->id(), 403);

        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        abort_unless($category->user_id === auth()->id(), 403);
        
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50'],
        ]);

        $exists = Category::where('user_id', auth()->id())
           ->where('name', $data['name'])
           ->where('id', '!', $category->id)
           ->exists();
        
        if ($exists) {
            return back()->withErrors(['name' => 'You already have this category.'])->withInput();
        }

        $category->update($data);

        return redirect()->route('categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        abort_unless($category->user_id === auth()->id(), 403);

        if ($category->expenses()->exists()) {
            return back()->withErrors(['name' => 'Cannot delete: category has expenses.']);
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted.');
    }
}