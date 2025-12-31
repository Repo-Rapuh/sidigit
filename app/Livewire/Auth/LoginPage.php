<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth')]
#[Title('Masuk')]
class LoginPage extends Component
{
    public string $username = '';

    public string $password = '';

    public bool $remember = false;

    protected function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function login()
    {
        $validated = $this->validate();

        $loginField = filter_var($validated['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $loginField => $validated['username'],
            'password' => $validated['password'],
        ];


        if (Auth::attempt($credentials, $this->remember)) {
            session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        $this->addError('username', 'Username/Email atau Password salah.');
        $this->password = '';
    }

    public function render()
    {
        return view('livewire.auth.login-page')
            ->layoutData([
                'badge' => 'Secure Access',
                'aside' => view('livewire.auth.partials.login-aside'),
                'title' => 'Masuk',
            ]);
    }
}
