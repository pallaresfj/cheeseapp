<x-filament::page>
    <div class="max-w-4xl w-full mx-auto">
        {{ $this->form }}
        <x-filament::button wire:click="submit" type="submit" class="w-full mt-4">
            Cargar Sucursal
        </x-filament::button>
    </div>
</x-filament::page>