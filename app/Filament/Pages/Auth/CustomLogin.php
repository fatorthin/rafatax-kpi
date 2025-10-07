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
use Illuminate\Validation\ValidationException;

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

            // Validasi input
            $this->validate([
                'data.email' => 'required|email',
                'data.password' => 'required',
            ]);

            // Coba login
            if (!Auth::attempt([
                'email' => $data['email'],
                'password' => $data['password'],
            ], $data['remember'] ?? false)) {
                // Jika login gagal, throw validation exception
                throw ValidationException::withMessages([
                    'data.email' => [__('filament-panels::pages/auth/login.messages.failed')],
                ]);
            }

            // Pastikan session sudah disimpan
            session()->regenerate();

            // Jika login berhasil, return custom login response
            return app(CustomLoginResponse::class);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            // Log error untuk debugging
            \Log::error('Login error: ' . $e->getMessage());

            throw ValidationException::withMessages([
                'data.email' => [__('filament-panels::pages/auth/login.messages.failed')],
            ]);
        }
    }
}
