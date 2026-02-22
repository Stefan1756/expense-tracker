<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl">Budget Settings</h2>
            <a href="{{ route('dashboard') }}" class="px-4 py-2 border rounded-md">Back</a>
        </div>
    </x-slot>
    <div class="py-6">
        <div class="max-w-xl mx-auto bg-white shadow rounded p-6">

        @if($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('budget.update') }}" class="space-y-4">
            @csrf 
            
            <div>
                <label class="block text-sm font-medium">Monthly Budget</label>
                <input type="number" step="0.01" name="monthly_budget"
                value="{{ old('monthly_budget', $user->monthly_budget) }}"
                class="w-full border rounded p-2"
                placeholder="e.g. 500000" />
            <p class="text-xs text-gray-600 mt-1">Leave empty to disable budget tracking.</p>
            </div>

            <button class="px-4 py-2 bg-indigo-600 text-white rounded">Save</button>
        </form>
        
        </div>
    </div>
</x-app-layout>