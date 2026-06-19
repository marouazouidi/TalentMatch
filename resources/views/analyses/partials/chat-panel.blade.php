@php
    $conversation ??= null;
    $messages ??= collect();
    $analysis ??= null;
@endphp

<div x-data="{ open: {{ $conversation ? 'true' : 'false' }} }" class="mt-6">
    <button
        @click="open = !open; if (open && !$refs.messagesContainer) { $wire?.loadConversation?.() }"
        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none transition ease-in-out duration-150"
    >
        {{ __('Open AI Assistant') }}
    </button>

    <div x-show="open" x-cloak class="mt-4 bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">{{ __('AI Assistant') }}</h3>
            <p class="text-sm text-gray-500">{{ __('Ask questions about this candidate analysis') }}</p>
        </div>

        <div x-ref="messagesContainer" class="p-4 space-y-4 max-h-96 overflow-y-auto">
            @forelse ($messages as $message)
                <div class="flex {{ $message->role === 'user' ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[75%] rounded-lg px-4 py-2 text-sm {{ $message->role === 'user' ? 'bg-indigo-100 text-indigo-900' : 'bg-gray-100 text-gray-900' }}">
                        <p class="whitespace-pre-wrap">{{ $message->content }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $message->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 text-center">{{ __('No messages yet. Start the conversation!') }}</p>
            @endforelse
        </div>

        <div class="p-4 border-t border-gray-200">
            <form method="POST" action="{{ route('conversations.messages.store', $conversation ?? 0) }}" class="flex gap-2">
                @csrf
                <x-text-input
                    name="content"
                    class="flex-1"
                    placeholder="{{ __('Type your question...') }}"
                    maxlength="2000"
                    required
                />
                <x-primary-button>{{ __('Send') }}</x-primary-button>
            </form>
            @error('content')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
