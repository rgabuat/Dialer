<?php

namespace App\Livewire\Stores;

use App\Models\Store;
use Livewire\Component;
use Livewire\WithPagination;

class StoresIndex extends Component
{
  use WithPagination;

  protected $paginationTheme = "tailwind";

  public $search = "";
  public int $perPage = 10;

  public $storeId;
  public $name;
  public $address;
  public $brand;

  public $isEdit = false;

  protected $rules = [
    "name" => "required|string|max:255",
    "address" => "required|string|max:255",
    "brand" => "required|string|max:255",
  ];

  public function updatedSearch(): void
  {
    $this->resetPage();
  }

  public function updatedPerPage(): void
  {
    $this->resetPage();
  }

  public function render()
  {
    return view("livewire.stores.stores-index", [
      "stores" => Store::query()
        ->where(function ($q) {
          $q->where("name", "like", "%{$this->search}%")
            ->orWhere("address", "like", "%{$this->search}%")
            ->orWhere("brand", "like", "%{$this->search}%");
        })
        ->latest()
        ->paginate($this->perPage),
    ])->layout("components.layouts.app");
  }
}
