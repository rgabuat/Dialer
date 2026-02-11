<?php

namespace App\Livewire\Users;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;

class UsersIndex extends Component
{
    use WithPagination;

    public string $search = '';

    protected $queryString = ['search'];

    public function render()
    {
        return view('livewire.users.users-index', [
            'users' => User::query()
                ->where(function ($q) {
                    $q->where('first_name', 'like', "%{$this->search}%")
                      ->orWhere('last_name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
                })
                ->latest()
                ->paginate(10),
        ])->layout('components.layouts.app');
    }
}
