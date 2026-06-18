<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $offer->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Description') }}</h3>
                        <p class="mt-1 text-gray-600">{{ $offer->description }}</p>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Required Skills') }}</h3>
                        <div class="mt-1 flex flex-wrap gap-2">
                            @foreach ($offer->required_skills as $skill)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    {{ $skill }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Minimum Experience') }}</h3>
                        <p class="mt-1 text-gray-600">{{ $offer->minimum_experience }} {{ __('years') }}</p>
                    </div>

                    <div class="flex items-center gap-4">
                        <a href="{{ route('offers.edit', $offer) }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none transition ease-in-out duration-150">
                            {{ __('Edit') }}
                        </a>
                        <form action="{{ route('offers.destroy', $offer) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none transition ease-in-out duration-150"
                                    onclick="return confirm('{{ __('Are you sure?') }}')">
                                {{ __('Delete') }}
                            </button>
                        </form>
                        <a href="{{ route('offers.index') }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Back to List') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
