<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div class="text-with-border-bottom">
        {{ $getState() }}
    </div>
</x-dynamic-component>
