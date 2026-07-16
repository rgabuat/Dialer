<?php

namespace App\Livewire\Permissions;

use Livewire\Component;
use Spatie\Permission\Models\Permission;

class PermissionsIndex extends Component
{
  public string $name = "";

  public function create()
  {
    $this->validate([
      "name" => "required|unique:permissions,name",
    ]);

    Permission::create(["name" => $this->name]);
    $this->name = "";
  }

  public function render()
  {
    return view("livewire.permissions.permissions-index", [
      "permissions" => Permission::all(),
    ])->layout("components.layouts.admin", ['heading' => 'Permissions']);
  }
}
