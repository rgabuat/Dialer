<?php

use Livewire\Livewire;
use App\Livewire\Leads\LeadEdit;
use App\Livewire\Users\UserEdit;
use App\Livewire\Roles\RolesEdit;
use App\Livewire\Leads\LeadCreate;
use App\Livewire\Leads\LeadsIndex;
use App\Livewire\Roles\RolesIndex;
use App\Livewire\Settings\Profile;
use App\Livewire\Stores\StoreEdit;
use App\Livewire\Users\UserCreate;
use App\Livewire\Users\UsersIndex;
use App\Livewire\Roles\RolesCreate;
use App\Livewire\Settings\Password;
use App\Livewire\Stores\StoresIndex;
use Illuminate\Support\Facades\Route;
use App\Livewire\Settings\Preferences;
use App\Http\Controllers\TwilioController;
use App\Livewire\Permissions\PermissionsIndex;
use App\Livewire\Activitylogs\ActivitylogsIndex;
use App\Http\Controllers\Livewire\Auth\LoginController;
use App\Http\Controllers\Livewire\Settings\ProfileController;


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


    Route::get('/', function () {
        return view('livewire.app.dashboard');
    })->name('dashboard');

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    //settings
    
    Route::get('/settings/profile', Profile::class)->name('settings.profile');

    Route::get('/settings/knowledge', Profile::class)->name('settings.knowledge');
    Route::get('/settings/password', Password::class)->name('settings.password');

    Route::get('/settings/picture', Profile::class)->name('settings.picture');
    Route::get('/settings/preferences', Preferences::class)->name('settings.preferences');

    //users
    Route::get('/users', UsersIndex::class)->name('users.index');
    Route::get('/user/create', UserCreate::class)->name('user.create');
    Route::get('/user/{user}/edit', UserEdit::class)->name('user.edit');

    //activity logs
    Route::get('/activity-logs',ActivitylogsIndex::class)->name('activitylogs.index');
    Route::get('/activity-log/{log}/',ActivitylogsIndex::class)->name('activitylog.view');

    //roles and permission
    Route::get('/roles',RolesIndex::class)->name('roles.index');
    Route::get('/role/create',RolesCreate::class)->name('roles.create');
    Route::get('/role/{role}/edit',RolesEdit::class)->name('roles.edit');

    Route::get('/permissions',PermissionsIndex::class)->name('permissions.index');

    //stores
    Route::get('/stores', StoresIndex::class)->name('stores.index');
    Route::get('/stores/{store}/edit', StoreEdit::class)->name('store.edit');

    //Leads
    Route::get('/leads', LeadsIndex::class)->name('leads.index');
    Route::get('/lead/create', LeadCreate::class)->name('lead.create');
    Route::get('/lead/{lead}/edit', LeadEdit::class)->name('lead.edit');

    /**
     * Twilio routes Controllers
     */

    Route::get('/phone/access-token', [TwilioController::class, 'getAccessToken'])->name('twilio.getAccessToken');



