<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        return view('budget.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'monthly_budget' => ['nullable', 'numeric', 'min:0'],
        ]);

        auth()->user()->update([
            'monthly_budget' => $data['monthly_budget'],
        ]);

        return redirect()->route('dashboard')->with('success', 'Budget updated.');
    }
}