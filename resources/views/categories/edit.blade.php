<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Category</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                @if($errors->any())
                    <div class="mb-4 p-3 rounded bg-red-100 text-red-800">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('categories.update', $category) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <input name="name" value="{{ old('name', $category->name) }}" class="mt-1 w-full border-gray-300 rounded-md" />
                    </div>

                    <div class="flex gap-2">
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Update</button>
                        <a href="{{ route('categories.index') }}" class="px-4 py-2 border rounded-md">Back</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>