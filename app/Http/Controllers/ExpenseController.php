<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Category;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();

        $month = $request->get('month', now()->format('Y-m'));

        $start = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = \Carbon\Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        $expenses = Expense::with('category')
            ->where('user_id', $userId)
            ->whereBetween('spent_at', [$start, $end])
            ->orderByDesc('spent_at')
            ->paginate(10);
        
        $categories = Category::where('user_id', $userId)->get();

        return view('expenses.index', compact('expenses', 'categories', 'month'));
    }

    public function create()
    {
        $categories = Category::where('user_id', auth()->id())->get();

        return view('expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'spent_at' => 'required|date',
            'category_id' => 'required|exists:categories,id',
            'note' => 'nullable|string'
        ]);

        Expense::create([
            'user_id' => auth()->id(),
            ...$data
        ]);

        return redirect()->route('expenses.index')->with('success', 'Expense added.');
    }

    public function edit(Expense $expense)
    {
        abort_unless($expense->user_id === auth()->id(), 403);

        $categories = Category::where('user_id', auth()->id())->get();

        return view('expenses.edit', compact('expense', 'categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        abort_unless($expense->user_id === auth()->id(), 403);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'spent_at' => 'required|date',
            'category_id' => 'required|exists:categories,id',
            'note' => 'nullable|string'
        ]);

        $expense->update($data);

        return redirect()->route('expenses.index')->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense)
    {
        abort_unless($expense->user_id === auth()->id(), 403);

        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense deleted.');
    }
}