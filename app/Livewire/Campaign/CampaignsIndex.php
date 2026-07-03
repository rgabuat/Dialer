<?php

namespace App\Livewire\Campaign;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Campaign;
use App\Models\User;
use App\Events\CampaignDeleted;

class CampaignsIndex extends Component
{
  use WithPagination;

  public string $search = "";
  public int $perPage = 20;

  public ?int $deletingCampaignId = null;
  public string $deleteInput = '';
  public string $expectedDeleteText = '';
  public string $deletingCampaignName = '';

  public function updatingSearch(): void
  {
    $this->resetPage();
  }

  public function updatedPerPage(): void
  {
    $this->resetPage();
  }

  public function openDeleteModal(int $id): void
  {
    $campaign = Campaign::find($id);
    if (!$campaign) return;

    $this->deletingCampaignId   = $id;
    $this->deletingCampaignName = $campaign->name;
    $this->expectedDeleteText   = $campaign->name;
    $this->deleteInput          = '';
  }

  public function closeDeleteModal(): void
  {
    $this->deletingCampaignId   = null;
    $this->deleteInput          = '';
    $this->expectedDeleteText   = '';
    $this->deletingCampaignName = '';
  }

  public function delete(): void
  {
    if ($this->deleteInput !== $this->expectedDeleteText) {
      $this->addError('deleteInput', 'Confirmation text does not match.');
      return;
    }

    $campaign = Campaign::find($this->deletingCampaignId);
    if (!$campaign) {
      $this->closeDeleteModal();
      return;
    }

    $campaignId = $campaign->id;
    $groupIds   = $campaign->userGroups()->pluck('id');
    $userIds    = User::whereIn('user_group_id', $groupIds)->pluck('id');

    $campaign->delete();

    foreach ($userIds as $userId) {
      broadcast(new CampaignDeleted($userId, $campaignId));
    }

    $this->closeDeleteModal();
    session()->flash('success', "Campaign \u201c{$this->deletingCampaignName}\u201d deleted.");
  }

  public function render()
  {
    $campaigns = Campaign::when(
      $this->search,
      fn($q) => $q
        ->where("name", "like", "%{$this->search}%")
        ->orWhere("description", "like", "%{$this->search}%")
    )
      ->withCount('inGroups')
      ->latest()
      ->paginate($this->perPage);

    return view(
      "livewire.campaign.campaigns-index",
      compact("campaigns")
    )->layout("components.layouts.app");
  }
}
