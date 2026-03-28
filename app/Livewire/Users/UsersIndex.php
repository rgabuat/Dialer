<?php

namespace App\Livewire\Users;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;

class UsersIndex extends Component
{
  use WithPagination;

  public string $search = "";
  public int $perPage = 10;

  protected $queryString = ["search"];

  public function updatedPerPage(): void
  {
    $this->resetPage();
  }

  public function render()
  {
    return view("livewire.users.users-index", [
      "users" => User::query()
        ->where(function ($q) {
          $q->where("first_name", "like", "%{$this->search}%")
            ->orWhere("last_name", "like", "%{$this->search}%")
            ->orWhere("email", "like", "%{$this->search}%");
        })
        ->latest()
        ->paginate($this->perPage),
    ])->layout("components.layouts.app");
  }
}
