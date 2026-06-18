<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BedController;
use App\Http\Controllers\SearchController;
use App\Http\Middleware\SetLocale;
use App\Livewire\Booking\BookingShow;
use App\Livewire\Booking\CreateBooking;
use App\Livewire\Booking\MyBookings;
use App\Livewire\Checkin\CheckinForm;
use App\Livewire\Checkin\CheckoutForm;
use App\Livewire\Complaints\CreateComplaint;
use App\Livewire\Extensions\ManageExtension;
use App\Livewire\Extensions\RequestExtension;
use App\Livewire\Favorites\FavoritesList;
use App\Livewire\Host\Dashboard as HostDashboard;
use App\Livewire\Host\HostBookings;
use App\Livewire\Host\HostEarnings;
use App\Livewire\Host\ManageBooking;
use App\Livewire\Messages\ChatWindow;
use App\Livewire\Messages\ConversationList;
use App\Livewire\Pages\HealthPage;
use App\Livewire\Pages\HomePage;
use App\Livewire\Profile\EditProfile;
use App\Livewire\Profile\ShowProfile;
use App\Livewire\Reviews\CreateReview;
use App\Livewire\SavedSearches\SavedSearchesList;
use App\Livewire\Waitlist\MyWaitlist;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/en');
Route::redirect('/search', '/en/search');
Route::redirect('/health', '/en/health');

Route::pattern('locale', 'en|ru');

Route::prefix('{locale}')
    ->whereIn('locale', ['en', 'ru'])
    ->middleware(SetLocale::class)
    ->group(function (): void {
        Route::get('/', HomePage::class)->name('home');
        Route::get('/health', HealthPage::class)->name('health');

        Route::prefix('search')->name('search.')->group(function (): void {
            Route::get('/', SearchController::class)->name('index');
        });

        Route::prefix('beds')->name('beds.')->group(function (): void {
            Route::get('/{bed}', [BedController::class, 'show'])->name('show');
        });
    });

Route::middleware('guest')->prefix('auth')->name('auth.')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/auth/logout', [LoginController::class, 'destroy'])->name('auth.logout');
});

Route::prefix('{locale}')
    ->whereIn('locale', ['en', 'ru'])
    ->middleware([SetLocale::class, 'auth'])
    ->group(function (): void {

        Route::prefix('bookings')->name('guest.bookings.')->group(function (): void {
            Route::get('/', MyBookings::class)->name('index');
            Route::get('/{booking}', BookingShow::class)->name('show');
        });

        Route::get('/beds/{bed}/book', CreateBooking::class)->name('beds.book');

        Route::prefix('profile')->name('profile.')->group(function (): void {
            Route::get('/edit', EditProfile::class)->name('edit');
        });

        Route::get('/user/{user}', ShowProfile::class)->name('profile.show');

        Route::prefix('messages')->name('messages.')->group(function (): void {
            Route::get('/', ConversationList::class)->name('index');
            Route::get('/{conversation}', ChatWindow::class)->name('show');
        });

        Route::prefix('favorites')->name('favorites.')->group(function (): void {
            Route::get('/', FavoritesList::class)->name('index');
        });

        Route::get('/bookings/{booking}/review', CreateReview::class)->name('reviews.create');
        Route::get('/bookings/{booking}/complaint', CreateComplaint::class)->name('complaints.create');
        Route::get('/bookings/{booking}/extend', RequestExtension::class)->name('bookings.extend');
        Route::get('/bookings/{booking}/checkin', CheckinForm::class)->name('bookings.checkin');
        Route::get('/bookings/{booking}/checkout', CheckoutForm::class)->name('bookings.checkout');

        Route::get('/saved-searches', SavedSearchesList::class)->name('saved-searches.index');
        Route::get('/waitlist', MyWaitlist::class)->name('waitlist.index');

        Route::prefix('host')->name('host.')->group(function (): void {
            Route::get('/', HostDashboard::class)->name('dashboard');
            Route::get('/bookings', HostBookings::class)->name('bookings.index');
            Route::get('/bookings/{booking}', ManageBooking::class)->name('bookings.manage');
            Route::get('/bookings/{booking}/extension/{extension}', ManageExtension::class)->name('extensions.manage');
            Route::get('/earnings', HostEarnings::class)->name('earnings');
        });
    });
