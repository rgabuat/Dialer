<?php

namespace App\Livewire\Roles;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class RolesIndex extends Component
{
  use WithPagination;

  public int $perPage = 15;

  public function updatedPerPage(): void
  {
    $this->resetPage();
  }

  public function delete(Role $role)
  {
    $role->delete();
  }

  public function render()
  {
    return view("livewire.roles.roles-index", [
      "roles" => Role::withCount("permissions")->paginate($this->perPage),
    ])->layout("components.layouts.app");
  }
}
