<?php

namespace App\Livewire\Stores;

use Livewire\Component;
use App\Models\Store;

class StoreEdit extends Component
{
    public Store $store;

    // Store fields
    public string $name = '';
    public string $address = '';
    public string $brand = '';

    public $confirmingDelete = false;

    public function confirmDelete()
    {
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        $this->store->delete();

        return redirect()
            ->route('stores.index')
            ->with('success', 'Store deleted.');
    }

    public function mount(Store $store)
    {
        $this->store = $store;
        $this->name = $store->name;
        $this->address = $store->address;
        $this->brand = $store->brand;
    }

    protected $rules = [
        'store.name' => 'required|string|max:255',
        'store.address' => 'required|string|max:255',
        'store.brand' => 'required|string|max:255',
    ];

    public function save()
    {
        $this->validate();
        $this->store->save();

        session()->flash('success', 'Store updated successfully.');
    }

    public function render()
    {
        return view('livewire.stores.store-edit')
            ->layout('components.layouts.app');
    }
}
