<?php

use App\Http\Controllers\Livewire\Auth\LoginController;
use App\Http\Controllers\TwilioController;
use App\Livewire\Activity\OverviewIndex;
use App\Livewire\Activity\ShiftMonitoring;
use App\Livewire\Activitylogs\ActivitylogsIndex;
use App\Livewire\Agent\AgentStatusIndex;
use App\Livewire\Callbacks\CallbackPanel;
use App\Livewire\CallLists\CallListCreate;
use App\Livewire\CallLists\CallListEdit;
use App\Livewire\CallLists\CallListsIndex;
use App\Livewire\Campaign\CampaignCreate;
use App\Livewire\Campaign\CampaignEdit;
use App\Livewire\Campaign\CampaignSelect;
use App\Livewire\Campaign\CampaignsIndex;
use App\Livewire\Conversations\ConversationShow;
use App\Livewire\Conversations\ConversationsIndex;
use App\Livewire\Dids\CidGroupEdit;
use App\Livewire\Dids\CidGroupsIndex;
use App\Livewire\Dids\CidNumbersIndex;
use App\Livewire\Dids\DidCreate;
use App\Livewire\Dids\DidEdit;
use App\Livewire\Dids\DidsIndex;
use App\Livewire\Dispositions\DispositionCreate;
use App\Livewire\Dispositions\DispositionEdit;
use App\Livewire\Dispositions\DispositionsIndex;
use App\Livewire\InGroups\InGroupCreate;
use App\Livewire\InGroups\InGroupEdit;
use App\Livewire\InGroups\InGroupsIndex;
use App\Livewire\InGroups\QueueMonitor;
use App\Livewire\IvrMenus\IvrMenuCreate;
use App\Livewire\IvrMenus\IvrMenuEdit;
use App\Livewire\IvrMenus\IvrMenusIndex;
use App\Livewire\Leads\LeadCreate;
use App\Livewire\Leads\LeadEdit;
use App\Livewire\Leads\LeadsIndex;
use App\Livewire\Permissions\PermissionsIndex;
use App\Livewire\Reports\ReportsOverview;
use App\Livewire\Roles\RolesCreate;
use App\Livewire\Roles\RolesEdit;
use App\Livewire\Roles\RolesIndex;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Phone;
use App\Livewire\Settings\Preferences;
use App\Livewire\Settings\Profile;
use App\Livewire\Stores\StoreEdit;
use App\Livewire\Stores\StoresIndex;
use App\Livewire\UserGroups\UserGroupCreate;
use App\Livewire\UserGroups\UserGroupEdit;
use App\Livewire\UserGroups\UserGroupsIndex;
use App\Livewire\Users\UserCreate;
use App\Livewire\Users\UserEdit;
use App\Livewire\Users\UserShow;
use App\Livewire\Users\UsersIndex;
use App\Livewire\Workforce\RosterCreate;
use App\Livewire\Workforce\RosterIndex;
use App\Livewire\Workforce\RosterShow;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Livewire::setScriptRoute(function ($handle) {
    return Route::get('/livewire/livewire.js', $handle);
});

Route::middleware(['guest'])->group(function () {
    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::middleware(['auth'])->group(function () {
    // Campaign selection — exempt from campaign.selected check
    Route::get('/campaign/select', CampaignSelect::class)->name(
        'campaign.select'
    );
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Users — no campaign context needed (accessible from admin panel)
    Route::middleware('permission:page.users')->group(function () {
        Route::get('/users', UsersIndex::class)->name('users.index');
        Route::get('/user/create', UserCreate::class)->name('user.create');
        Route::get('/user/{user}', UserShow::class)->name('user.show');
        Route::get('/user/{user}/edit', UserEdit::class)->name('user.edit');
    });

    // Roles & Permissions — no campaign context needed
    Route::middleware('permission:page.roles')->group(function () {
        Route::get('/roles', RolesIndex::class)->name('roles.index');
        Route::get('/role/create', RolesCreate::class)->name('roles.create');
        Route::get('/role/{role}/edit', RolesEdit::class)->name('roles.edit');
        Route::get('/permissions', PermissionsIndex::class)->name('permissions.index');
    });

    // User Groups — no campaign context needed
    Route::middleware('permission:page.user_groups')->group(function () {
        Route::get('/user-groups', UserGroupsIndex::class)->name('user-groups.index');
        Route::get('/user-group/create', UserGroupCreate::class)->name('user-group.create');
        Route::get('/user-group/{group}/edit', UserGroupEdit::class)->name('user-group.edit');
    });

    // All other authenticated routes require a campaign to be selected
    Route::middleware(['campaign.selected'])->group(function () {
        Route::get('/', function () {
            return view('livewire.app.dashboard');
        })->name('dashboard');

        // Settings — available to all authenticated users, no page permission required
        Route::get('/settings/profile', Profile::class)->name('settings.profile');
        Route::get('/settings/knowledge', Profile::class)->name('settings.knowledge');
        Route::get('/settings/password', Password::class)->name('settings.password');
        Route::get('/settings/picture', Profile::class)->name('settings.picture');
        Route::get('/settings/preferences', Preferences::class)->name('settings.preferences');
        Route::get('/settings/phone', Phone::class)->name('settings.phone');

        // Campaign
        Route::middleware('permission:page.campaigns')->group(function () {
            Route::get('/campaigns', CampaignsIndex::class)->name('campaigns.index');
            Route::get('/campaign/create', CampaignCreate::class)->name('campaign.create');
            Route::get('/campaign/{campaign}/edit', CampaignEdit::class)->name('campaign.edit');
        });

        // Call Lists
        Route::middleware('permission:page.call_lists')->group(function () {
            Route::get('/campaign/{campaign}/lists', CallListsIndex::class)->name('campaign.lists');
            Route::get('/campaign/{campaign}/list/create', CallListCreate::class)->name('campaign.list.create');
            Route::get('/campaign/{campaign}/list/{callList}/edit', CallListEdit::class)->name('campaign.list.edit');
        });

        // Dispositions
        Route::middleware('permission:page.dispositions')->group(function () {
            Route::get('/campaign/{campaign}/dispositions', DispositionsIndex::class)->name('campaign.dispositions');
            Route::get('/campaign/{campaign}/disposition/create', DispositionCreate::class)->name('campaign.disposition.create');
            Route::get('/campaign/{campaign}/disposition/{disposition}/edit', DispositionEdit::class)->name('campaign.disposition.edit');
        });

        // Callbacks
        Route::middleware('permission:page.callbacks')->group(function () {
            Route::get('/callbacks', CallbackPanel::class)->name('callbacks.index');
        });

        // Activity Overview
        Route::middleware('permission:page.activity_overview')->group(function () {
            Route::get('/activity-overview', OverviewIndex::class)->name('activity.overview');
        });

        // Activity Logs
        Route::middleware('permission:page.activity_logs')->group(function () {
            Route::get('/activity-logs', ActivitylogsIndex::class)->name('activitylogs.index');
            Route::get('/activity-log/{log}/', ActivitylogsIndex::class)->name('activitylog.view');
        });

        // Agent Status
        Route::middleware('permission:page.agent_status')->group(function () {
            Route::get('/agent-status', AgentStatusIndex::class)->name('agent.status.index');
        });

        // Shift Monitoring
        Route::middleware('permission:page.shift_monitoring')->group(function () {
            Route::get('/shift-monitoring', ShiftMonitoring::class)->name('shift.monitoring');
        });

        // Leads
        Route::middleware('permission:page.leads')->group(function () {
            Route::get('/leads', LeadsIndex::class)->name('leads.index');
            Route::get('/lead/create', LeadCreate::class)->name('lead.create');
            Route::get('/lead/{lead}/edit', LeadEdit::class)->name('lead.edit');
        });

        // Stores
        Route::middleware('permission:page.stores')->group(function () {
            Route::get('/stores', StoresIndex::class)->name('stores.index');
            Route::get('/stores/{store}/edit', StoreEdit::class)->name('store.edit');
        });

        // Reports
        Route::middleware('permission:page.reports')->group(function () {
            Route::get('/reports', ReportsOverview::class)->name('reports.index');
        });

        // Conversations
        Route::middleware('permission:page.conversations')->group(function () {
            Route::get('/conversations', ConversationsIndex::class)->name('conversations.index');
            Route::get('/conversations/assigned', ConversationsIndex::class)->name('conversations.assigned');
            Route::get('/conversations/{conversation}', ConversationShow::class)->name('conversations.show');
        });

        // Workforce
        Route::middleware('permission:page.workforce')->group(function () {
            Route::get('/workforce/rosters', RosterIndex::class)->name('workforce.rosters.index');
            Route::get('/workforce/roster/create', RosterCreate::class)->name('workforce.roster.create');
            Route::get('/workforce/roster/{roster}', RosterShow::class)->name('workforce.roster.show');
        });

        // In-Groups
        Route::middleware('permission:page.in_groups')->group(function () {
            Route::get('/in-groups', InGroupsIndex::class)->name('in-groups.index');
            Route::get('/in-group/create', InGroupCreate::class)->name('in-group.create');
            Route::get('/in-group/{inGroup}/edit', InGroupEdit::class)->name('in-group.edit');
        });

        // Queue Monitor
        Route::middleware('permission:page.queue_monitor')->group(function () {
            Route::get('/queue-monitor', QueueMonitor::class)->name('queue.monitor');
        });

        // DIDs
        Route::middleware('permission:page.dids')->group(function () {
            Route::get('/dids', DidsIndex::class)->name('dids.index');
            Route::get('/did/create', DidCreate::class)->name('did.create');
            Route::get('/did/{did}/edit', DidEdit::class)->name('did.edit');
        });

        // CID Numbers
        Route::middleware('permission:page.cid_numbers')->group(function () {
            Route::get('/cid-numbers', CidNumbersIndex::class)->name('cid-numbers.index');
            Route::get('/cid-groups', CidGroupsIndex::class)->name('cid-groups.index');
            Route::get('/cid-group/{cidGroup}/edit', CidGroupEdit::class)->name('cid-group.edit');
        });

        // IVR Menus
        Route::middleware('permission:page.ivr_menus')->group(function () {
            Route::get('/ivr-menus', IvrMenusIndex::class)->name('ivr-menus.index');
            Route::get('/ivr-menu/create', IvrMenuCreate::class)->name('ivr-menu.create');
            Route::get('/ivr-menu/{ivrMenu}/edit', IvrMenuEdit::class)->name('ivr-menu.edit');
        });

        // Twilio (internal API endpoint, no page permission required)
        Route::get('/phone/access-token', [
            TwilioController::class,
            'getAccessToken',
        ])->name('twilio.getAccessToken');
    });
});

// ──────────────────────────────────────────────────────────────────────────────
//  Admin Panel
// ──────────────────────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {

    // Guest-only: show login page
    Route::middleware('guest')->group(function () {
        Route::get('/login', [\App\Http\Controllers\Admin\AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [\App\Http\Controllers\Admin\AdminAuthController::class, 'login'])->name('login.post');
    });

    // Authenticated + Super Admin only
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('/logout', [\App\Http\Controllers\Admin\AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');

        // Campaign management
        Route::get('/campaigns/create', \App\Livewire\Admin\AdminCampaignCreate::class)->name('campaigns.create');
        Route::resource('campaigns', \App\Http\Controllers\Admin\AdminCampaignController::class)
            ->except(['show', 'create']);

        // Lead template management
        Route::get('/lead-templates', [\App\Http\Controllers\Admin\AdminLeadTemplateController::class, 'index'])->name('lead-templates.index');
        Route::delete('/lead-templates/{leadTemplate}', [\App\Http\Controllers\Admin\AdminLeadTemplateController::class, 'destroy'])->name('lead-templates.destroy');

        // CID group management
        Route::get('/cid-groups', [\App\Http\Controllers\Admin\AdminCidGroupController::class, 'index'])->name('cid-groups.index');
        Route::delete('/cid-groups/{cidGroup}', [\App\Http\Controllers\Admin\AdminCidGroupController::class, 'destroy'])->name('cid-groups.destroy');

        // CID numbers manager (Twilio sync + local pool)
        Route::get('/cid-numbers', \App\Livewire\Admin\CidNumbersManager::class)->name('cid-numbers.index');

        // Inbound group management
        Route::get('/in-groups', [\App\Http\Controllers\Admin\AdminInGroupController::class, 'index'])->name('in-groups.index');
        Route::delete('/in-groups/{inGroup}', [\App\Http\Controllers\Admin\AdminInGroupController::class, 'destroy'])->name('in-groups.destroy');
    });
});
