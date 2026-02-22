<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl">Add Expense</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-xl mx-auto bg-white shadow rounded p-6">

        <form method="POST" action="{{ route('expenses.store') }}" class="space-y-4">
            @csrf 

            <input name="title" placeholder="Title" class="w-full border rounded p-2">
            <input type="number" step="0.01" name="amount" placeholder="Amount" class="w-full border rounded p-2">
            <input type="date" name="spent_at" class="w-full border rounded p-2">

            <select name="category_id" class="w-full border rounded p-2">
                <option value="">Select Category</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
            </select>

            <textarea name="note" placeholder="Note (optional)" class="w-full border rounded p-2"></textarea>

            <button class="px-4 py-2 bg-indigo-600 text-white rounded">Save</button>
        </form>
        
        </div>
    </div>
</x-app-layout>