<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Job Offer') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('offers.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-gray-700">{{ __('Title') }}</label>
                            <input id="title" type="text" name="title" value="{{ old('title') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">{{ __('Description') }}</label>
                            <textarea id="description" name="description" rows="5"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="required_skills" class="block text-sm font-medium text-gray-700">{{ __('Required Skills') }}</label>
                            <div id="skills-container">
                                @if (old('required_skills'))
                                    @foreach (old('required_skills') as $skill)
                                        <div class="flex items-center mt-1">
                                            <input type="text" name="required_skills[]" value="{{ $skill }}"
                                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <button type="button" class="ml-2 text-red-600 hover:text-red-900" onclick="this.parentElement.remove()">{{ __('Remove') }}</button>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="flex items-center mt-1">
                                        <input type="text" name="required_skills[]" placeholder="{{ __('e.g. PHP, Laravel') }}"
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                @endif
                            </div>
                            <button type="button" onclick="addSkillInput()" class="mt-2 text-sm text-indigo-600 hover:text-indigo-900">
                                + {{ __('Add Skill') }}
                            </button>
                            @error('required_skills')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="minimum_experience" class="block text-sm font-medium text-gray-700">{{ __('Minimum Experience (years)') }}</label>
                            <input id="minimum_experience" type="number" name="minimum_experience" value="{{ old('minimum_experience') }}" min="0"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('minimum_experience')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none transition ease-in-out duration-150">
                                {{ __('Create') }}
                            </button>
                            <a href="{{ route('offers.index') }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function addSkillInput() {
            const container = document.getElementById('skills-container');
            const div = document.createElement('div');
            div.className = 'flex items-center mt-1';
            div.innerHTML = '<input type="text" name="required_skills[]" placeholder="{{ __('e.g. PHP, Laravel') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">' +
                            '<button type="button" class="ml-2 text-red-600 hover:text-red-900" onclick="this.parentElement.remove()">{{ __('Remove') }}</button>';
            container.appendChild(div);
        }
    </script>
    @endpush
</x-app-layout>
