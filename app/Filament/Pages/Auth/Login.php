<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Filament\Http\Responses\Auth\CustomLoginResponse;

class Login extends BaseLogin
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent()
                    ->label('Email')
                    ->placeholder('Masukkan email Anda'),
                $this->getPasswordFormComponent()
                    ->label('Password')
                    ->placeholder('Masukkan password Anda')
                    ->revealable(),
                $this->getRememberFormComponent()
                    ->label('Ingat saya'),
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

            $user = Auth::user();
            
            // Redirect berdasarkan role
            return new CustomLoginResponse();

        } catch (\Exception $e) {
            $this->throwFailureValidationException();
        }

        return null;
    }
}
