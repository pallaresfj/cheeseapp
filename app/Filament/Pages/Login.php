<?php

namespace App\Filament\Pages;
 
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
 
class Login extends \Filament\Pages\Auth\Login
{
    protected function getEmailFormComponent(): Component
    {
        // Modify an existing field to give it a different label
        return TextInput::make('email')
            ->label('Usuario o Correo')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }
 
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/login.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/login.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/login.notifications.throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]) : null)
                ->danger()
                ->send();
 
            return null;
        }
 
        $data = $this->form->getState();
 
        // Attempt to authenticate with email or username in two calls
        // First one is for default email
        // Second one overrides to use the username
        if (!Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false) && !Filament::auth()->attempt($this->getCredentialsFromFormDataForUsernameLogin($data), $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }
 
        $user = Filament::auth()->user();
 
        if (
            ($user instanceof FilamentUser) &&
            (!$user->canAccessPanel(Filament::getCurrentPanel()))
        ) {
            Filament::auth()->logout();
 
            $this->throwFailureValidationException();
        }
 
        session()->regenerate();
 
        return app(LoginResponse::class);
    }
 
    private function getCredentialsFromFormDataForUsernameLogin(array $data): array
    {
        return [
            'username' => $data['email'],
            'password' => $data['password'],
        ];
    }
}
