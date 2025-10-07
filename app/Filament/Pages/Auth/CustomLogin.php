<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Checkbox;
use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use App\Filament\Http\Responses\Auth\CustomLoginResponse;

class CustomLogin extends BaseLogin
{
    protected static string $view = 'filament.pages.auth.custom-login';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->autocomplete()
                    ->autofocus()
                    ->extraInputAttributes(['tabindex' => 1])
                    ->placeholder('Email'),
                
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->required()
                    ->extraInputAttributes(['tabindex' => 2])
                    ->placeholder('Password'),
                
                Checkbox::make('remember')
                    ->label('Remember me')
                    ->extraInputAttributes(['tabindex' => 3]),
            ])
            ->statePath('data');
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);

            $data = $this->form->getState();

            if (!Auth::attempt([
                'email' => $data['email'],
                'password' => $data['password'],
            ], $data['remember'] ?? false)) {
                throw new \Exception(__('filament-panels::pages/auth/login.messages.failed'));
            }

            /** @var \App\Models\User $user */
            $user = Auth::user();

            // Tentukan target redirect sesuai role
            $targetRoute = 'filament.admin.pages.admin-dashboard';
            if ($user && $user->hasRole('staff')) {
                $targetRoute = 'filament.staff.pages.dashboard';
            }

            // Gunakan Livewire redirect agar SPA langsung berpindah tanpa refresh
            $this->redirectIntended(route($targetRoute), navigate: true);

            return null; // Biarkan Livewire menangani redirect

        } catch (\Exception $e) {
            $this->throwFailureValidationException();
        }

        return null;
    }
}
