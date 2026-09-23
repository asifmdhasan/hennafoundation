<section class="overflow-hidden rounded-2xl p-7 text-white shadow-lg sm:p-9" style="background: linear-gradient(120deg, #9f1239 0%, #be123c 55%, #a16207 100%)">
    <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-center">
        <div class="max-w-2xl">
            <p class="mb-2 text-sm font-semibold uppercase tracking-[0.18em] text-rose-100">Henna Foundation · Content studio</p>
            <h2 class="mb-2 text-3xl font-bold tracking-tight text-white">Welcome back, {{ auth()->user()?->name }}</h2>
            <p class="m-0 text-base leading-7 text-rose-50">Update homepage sections, manage projects and publish stories from one place.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <x-filament::button tag="a" :href="\App\Filament\Pages\ManageHomepage::getUrl()" color="gray" icon="heroicon-o-pencil-square">
                Edit homepage
            </x-filament::button>
            <x-filament::button tag="a" :href="\App\Filament\Resources\Media\MediaResource::getUrl()" color="gray" icon="heroicon-o-photo">
                Upload media
            </x-filament::button>
        </div>
    </div>
</section>
