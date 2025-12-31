<?php

namespace App\Livewire\Layout;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;
use Livewire\Component;

class Header extends Component
{
    public ?Authenticatable $user = null;

    public function mount(): void
    {
        $this->user = auth()->user();
    }

    public function getInitialsProperty(): string
    {
        $name = (string) ($this->user?->name ?? '');

        if ($name === '') {
            return 'AD';
        }

        return Str::of($name)
            ->replaceMatches('/[^A-Za-z0-9]/u', '')
            ->substr(0, 2)
            ->upper()
            ->value() ?: 'AD';
    }

    public function render()
    {
        return view('livewire.layout.header', [
            'user' => $this->user,
            'initials' => $this->initials,
        ]);
    }
}

