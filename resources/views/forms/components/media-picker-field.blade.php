<div>
    <livewire:media-picker
        :state-path="$getStatePath()"
        :multiple="$isMultiple()"
        :state="$getState()"
        :key="'media-picker-'.$getStatePath()"
    />
</div>
