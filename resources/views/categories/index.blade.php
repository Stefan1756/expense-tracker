<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Categories</h2>
            <a href="{{ route('categories.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md">
                New Category
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-6">
            @if(session('success'))
                <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('success') }}</div>
            @endif

            @if($errors->any())
            <div class="mb-4 p-3 rounded bg-red-100 text-red-800">
                {{ $error->first() }}
            </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-6">
                @if($categories->isEmpty())
                   <p class="text-gray-500">No categories yet. Create one.</p>
                @else
                   <div class="space-y-3">
                    @foreach($categories as $category)
                        <div class="flex justify-between items-center border rounded p-3">
                            <div class="font-medium">{{ $category->name }}</div>
                            <div class="flex gap-2">
                                <a class="px-3 py-1 border rounded" href="{{ route('categories.edit', $category) }}">Edit</a>

                                <form method="POST" action="{{ route('categories.destroy', $category) }}"
                                    onsubmit="return confirm('Delete this category?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="px-3 py-1 bg-red-600 text-white rounded">Delete</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                   </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>