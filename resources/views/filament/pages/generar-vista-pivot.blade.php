<x-filament::page>
    <div class="max-w-xl mx-auto">
        {{ $this->form }}
        <x-filament::button wire:click="submit" type="submit" class="mt-4">
            Cargar Sucursal
        </x-filament::button>
    </div>
</x-filament::page>