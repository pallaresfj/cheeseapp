<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use App\Models\MilkPurchase;
use App\Models\Branch;
use App\Models\Setting;
use App\Filament\Resources\MilkPurchasesPivotViewResource;
use Illuminate\Support\Facades\Auth;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class GenerarVistaPivot extends Page implements HasForms
{
    use Forms\Concerns\InteractsWithForms;
    use HasPageShield;

    protected static string $view = 'filament.pages.generar-vista-pivot';
    protected static ?string $navigationLabel = 'Compras Semanales';
    protected static ?string $title = 'Compras Semanales';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Operaciones';
    

    public ?array $data = [];
    public ?int $branch_id = null;
    public ?string $start_date = null;

    public function mount(): void
    {
        $this->buildForm()->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make()
                ->schema([
                    Select::make('branch_id')
                        ->label('Sucursal')
                        ->placeholder('Seleccione sucursal')
                        ->options(Branch::where('active', true)->pluck('name', 'id'))
                        ->required()
                        ->native(false)
                        ->searchable()
                        ->reactive()
                        ->afterStateUpdated(function (callable $set, $state) {
                            $start = MilkPurchase::where('branch_id', $this->branch_id)
                                ->where('status', 'pending')
                                ->orderBy('date')
                                ->value('date');

                            $set('start_date', $start);
                        })
                        ->columnSpanFull(),

                    DatePicker::make('start_date')
                        ->label('Inicio de ciclo')
                        ->required()
                        ->columnSpanFull(),
                ]),
        ];
    }

    public function submit(): void
    {
        $state = $this->buildForm()->getState();
        $branchId = $state['branch_id'];
        $startDate = $state['start_date'];
        $ciclo = (int) Setting::where('key', 'facturacion.ciclo')->value('value') ?? 7;

        try {
            DB::statement("CALL generate_milk_purchases_pivot_view($branchId, '$startDate', $ciclo)");
            session(['pivot_branch_id' => $branchId]);

            Notification::make()
                ->title('Vista generada correctamente')
                ->success()
                ->send();

            $this->redirect(MilkPurchasesPivotViewResource::getUrl());
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error generando vista')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function buildForm(): Form
    {
        return $this->form(
            $this->makeForm()
                ->schema($this->getFormSchema())
                ->statePath(null)
        );
    }
}