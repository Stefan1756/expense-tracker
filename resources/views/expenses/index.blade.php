<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl">Expenses</h2>
            <a href="{{ route('expenses.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded">Add Expense</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto">
            <form method="GET" class="mb-4">
                <input type="month" name="month" value="{{ $month }}" class="border rounded p-2">
                <button class="px-4 py-2 bg-indigo-600 text-white rounded">Filter</button>
            </form>

            @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white shadow rounded p-4">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b">
                            <th>Title</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                        <tr class="border-b">
                            <td>{{ $expense->title }}</td>
                            <td>{{ $expense->category->name ?? ''}}</td>
                            <td>{{ $expense->spent_at->format('Y-m-d') }}</td>
                            <td>{{ number_format($expense->amount,2) }}</td>
                            <td class="flex gap-2">
                                <a href="{{ route('expenses.edit', $expense->id) }}" class="px-2 py-1 border rounded">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('expenses.destroy', $expense) }}">
                                @csrf
                                @method('DELETE')
                                <button class="px-2 py-1 bg-red-600 text-white rounded">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5">No expenses found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-4">
                    {{ $expenses->links()}}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>