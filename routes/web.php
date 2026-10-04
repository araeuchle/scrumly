<?php

use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Dashboard;
use App\Livewire\Impediments\Index as ImpedimentIndex;
use App\Livewire\Journal\Index as JournalIndex;
use App\Livewire\Magazine\Index as MagazineIndex;
use App\Livewire\Magazine\Show as MagazineShow;
use App\Livewire\Settings\Index as SettingsIndex;
use App\Livewire\SprintEvents\Show as SprintEventShow;
use App\Livewire\Sprints\Create as SprintCreate;
use App\Livewire\Sprints\Index as SprintIndex;
use App\Livewire\Sprints\Show as SprintShow;
use App\Livewire\TeamMembers\Show as TeamMemberShow;
use App\Livewire\Teams\EventTypes as TeamEventTypes;
use App\Livewire\Teams\Index as TeamIndex;
use App\Livewire\Teams\Members as TeamMembers;
use App\Livewire\Teams\Metrics as TeamMetrics;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::view('/', 'marketing.home')->name('home');

Route::get('/magazin', MagazineIndex::class)->name('magazine.index');
Route::get('/magazin/{post:slug}', MagazineShow::class)->name('magazine.show');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
    Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/journal', JournalIndex::class)->name('journal.index');

    Route::get('/teams', TeamIndex::class)->name('teams.index');
    Route::get('/teams/{team}/members', TeamMembers::class)->name('teams.members');
    Route::get('/teams/{team}/event-types', TeamEventTypes::class)->name('teams.event-types');
    Route::get('/teams/{team}/impediments', ImpedimentIndex::class)->name('teams.impediments');
    Route::get('/teams/{team}/metrics', TeamMetrics::class)->name('teams.metrics');
    Route::get('/teams/{team}/sprints', SprintIndex::class)->name('sprints.index');
    Route::get('/teams/{team}/sprints/create', SprintCreate::class)->name('sprints.create');
    Route::get('/sprints/{sprint}', SprintShow::class)->name('sprints.show');
    Route::get('/sprint-events/{sprintEvent}', SprintEventShow::class)->name('sprint-events.show');
    Route::get('/team-members/{teamMember}', TeamMemberShow::class)->name('team-members.show');
    Route::get('/settings', SettingsIndex::class)->name('settings');

    Route::post('/logout', function (Request $request): RedirectResponse {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    })->name('logout');
});
