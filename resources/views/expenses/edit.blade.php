<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl">Edit Expense</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-xl mx-auto bg-white shadow rounded p-6">
            @if($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                {{ $errors->first() }}
            </div>
            @endif

        <form method="POST" action="{{ route('expenses.update', $expense->id) }}" class="space-y-4">
            @csrf 
            @method('PUT')
             

            <input name="title" value="{{ old('title', $expense->title)}}" class="w-full border rounded p-2">

            <input type="number" step="0.01" name="amount" value="{{ old('amount', $expense->amount) }}" class="w-full border rounded p-2">

            <input type="date" name="spent_at" value="{{ old('spent_at', $expense->spent_at->format('Y-m-d')) }}" class="w-full border rounded p-2">

            <select name="category_id" class="w-full border rounded p-2">
            @foreach($categories as $category)
                <option value="{{ $category->id }}"
                    @selected(old('category_id', $expense->category_id) == $category->id)>
                {{ $category->name }}
            </option>
            @endforeach
            </select>

            <textarea name="note" placeholder="Note (optional)" class="w-full border rounded p-2"></textarea>

            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">Save</button>
            <a href="{{ route('expenses.index') }}" class="px-4 py-2 border rounded">Back</a>
        </form>
        
        </div>
    </div>
</x-app-layout>