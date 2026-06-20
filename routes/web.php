<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BedController;
use App\Http\Controllers\SearchController;
use App\Http\Middleware\SetLocale;
use App\Livewire\Account\AccountSettingsPage;
use App\Livewire\Account\GuestPreferenceEditPage;
use App\Livewire\Account\GuestPreferenceWizardPage;
use App\Livewire\Account\PrivacySettingsPage;
use App\Livewire\Account\ProfileSetupPage;
use App\Livewire\Account\SecuritySettingsPage;
use App\Livewire\Auth\ForgotPasswordPage;
use App\Livewire\Auth\LoginPage;
use App\Livewire\Auth\RegisterPage;
use App\Livewire\Booking\BookingReview;
use App\Livewire\Booking\CancelBooking;
use App\Livewire\Booking\CreateBooking;
use App\Livewire\Booking\PaymentPage;
use App\Livewire\Checkin\CheckIn;
use App\Livewire\Checkin\CheckOut;
use App\Livewire\Checkin\ProblemReport;
use App\Livewire\Compare\ComparePlaces;
use App\Livewire\Complaints\ComplaintDetail;
use App\Livewire\Complaints\CreateComplaint;
use App\Livewire\Extensions\ExtendStay;
use App\Livewire\Extensions\ManageExtension;
use App\Livewire\Host\BedForm;
use App\Livewire\Host\HostBookings;
use App\Livewire\Host\HostIncome;
use App\Livewire\Host\HostOnboardingPage;
use App\Livewire\Host\HostProfileEditPage;
use App\Livewire\Host\ManageBooking;
use App\Livewire\Host\PropertyForm;
use App\Livewire\Host\PropertyList;
use App\Livewire\Host\PropertyShow;
use App\Livewire\Host\RoomForm;
use App\Livewire\Host\SleepingPlaceForm;
use App\Livewire\Host\SleepingPlaceList;
use App\Livewire\Messages\ChatWindow;
use App\Livewire\Notifications\NotificationsPage;
use App\Livewire\Pages\HealthPage;
use App\Livewire\Pages\HomePage;
use App\Livewire\Places\ShowSleepingPlace;
use App\Livewire\Profile\EditProfile;
use App\Livewire\Profile\ShowProfile;
use App\Livewire\Reviews\CreateReview;
use App\Livewire\SavedSearches\SavedSearchesList;
use App\Livewire\Shell\FavoritesPage;
use App\Livewire\Shell\HostCalendarPage;
use App\Livewire\Shell\HostHomePage;
use App\Livewire\Shell\HostListingsPage;
use App\Livewire\Shell\HostProfilePage;
use App\Livewire\Shell\HostRequestsPage;
use App\Livewire\Shell\MessagesPage;
use App\Livewire\Shell\ProfilePage;
use App\Livewire\Trips\BookingDetail;
use App\Livewire\Trips\CurrentStay;
use App\Livewire\Trips\TripList;
use App\Livewire\Waitlist\MyWaitlist;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/en');
Route::redirect('/search', '/en/search');
Route::redirect('/health', '/en/health');

Route::pattern('locale', implode('|', config('localization.supported_locales')));

Route::prefix('{locale}')
    ->whereIn('locale', config('localization.supported_locales'))
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

        Route::prefix('places')->name('places.')->group(function (): void {
            Route::get('/{sleepingPlace}', ShowSleepingPlace::class)->name('show');
        });
    });

Route::middleware([SetLocale::class, 'guest'])->prefix('auth')->name('auth.')->group(function (): void {
    Route::get('/login', LoginPage::class)->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    Route::get('/register', RegisterPage::class)->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
    Route::get('/forgot-password', ForgotPasswordPage::class)->name('forgot-password');
});

Route::middleware([SetLocale::class, 'auth'])->group(function (): void {
    Route::post('/auth/logout', [LoginController::class, 'destroy'])->name('auth.logout');
});

Route::prefix('{locale}')
    ->whereIn('locale', config('localization.supported_locales'))
    ->middleware([SetLocale::class, 'auth'])
    ->group(function (): void {

        Route::prefix('bookings')->name('guest.bookings.')->group(function (): void {
            Route::get('/', TripList::class)->name('index');
            Route::get('/{booking}/payment', PaymentPage::class)->name('payment');
            Route::get('/{booking}/cancel', CancelBooking::class)->name('cancel');
            Route::get('/{booking}', BookingDetail::class)->name('show');
        });

        Route::get('/trips', TripList::class)->name('trips.index');
        Route::get('/trips/current', CurrentStay::class)->name('trips.current');
        Route::get('/trips/{scope}', TripList::class)
            ->whereIn('scope', ['upcoming', 'past', 'cancelled'])
            ->name('trips.scope');

        Route::get('/beds/{bed}/book', CreateBooking::class)->name('beds.book');
        Route::get('/places/{sleepingPlace}/book', BookingReview::class)->name('places.book');

        Route::prefix('profile')->name('profile.')->group(function (): void {
            Route::get('/', ProfilePage::class)->name('index');
            Route::get('/setup', ProfileSetupPage::class)->name('setup');
            Route::get('/preferences', GuestPreferenceEditPage::class)->name('preferences.edit');
            Route::get('/preferences/setup', GuestPreferenceWizardPage::class)->name('preferences.setup');
            Route::get('/edit', EditProfile::class)->name('edit');
        });

        Route::prefix('account')->name('account.')->group(function (): void {
            Route::get('/settings', AccountSettingsPage::class)->name('settings');
            Route::get('/privacy', PrivacySettingsPage::class)->name('privacy');
            Route::get('/security', SecuritySettingsPage::class)->name('security');
        });

        Route::get('/user/{user}', ShowProfile::class)->name('profile.show');

        Route::get('/notifications', NotificationsPage::class)->name('notifications.index');

        Route::prefix('messages')->name('messages.')->group(function (): void {
            Route::get('/', MessagesPage::class)->name('index');
            Route::get('/{thread}', ChatWindow::class)->name('show');
        });

        Route::prefix('favorites')->name('favorites.')->group(function (): void {
            Route::get('/', FavoritesPage::class)->name('index');
        });

        Route::get('/bookings/{booking}/review', CreateReview::class)->name('reviews.create');
        Route::get('/bookings/{booking}/complaint', CreateComplaint::class)->name('complaints.create');
        Route::get('/complaints/{complaint}', ComplaintDetail::class)->name('complaints.show');
        Route::get('/bookings/{booking}/extend', ExtendStay::class)->name('bookings.extend');
        Route::get('/bookings/{booking}/checkin', CheckIn::class)->name('bookings.checkin');
        Route::get('/bookings/{booking}/checkin/problem', ProblemReport::class)->name('bookings.checkin.problem');
        Route::get('/bookings/{booking}/checkout', CheckOut::class)->name('bookings.checkout');

        Route::get('/saved-searches', SavedSearchesList::class)->name('saved-searches.index');
        Route::get('/waitlist', MyWaitlist::class)->name('waitlist.index');
        Route::get('/compare', ComparePlaces::class)->name('compare.index');

        Route::prefix('host')->name('host.')->group(function (): void {
            Route::get('/', HostHomePage::class)->name('dashboard');
            Route::get('/listings', HostListingsPage::class)->name('listings.index');
            Route::get('/listings/{scope}', HostListingsPage::class)
                ->whereIn('scope', ['drafts', 'hidden'])
                ->name('listings.scope');
            Route::get('/calendar', HostCalendarPage::class)->name('calendar');
            Route::get('/requests', HostRequestsPage::class)->name('requests.index');
            Route::get('/profile', HostProfilePage::class)->name('profile');
            Route::get('/profile/onboarding', HostOnboardingPage::class)->name('profile.onboarding');
            Route::get('/profile/edit', HostProfileEditPage::class)->name('profile.edit');
            Route::get('/bookings', HostBookings::class)->name('bookings.index');
            Route::get('/bookings/{booking}', ManageBooking::class)->name('bookings.manage');
            Route::get('/bookings/{booking}/review', CreateReview::class)->name('reviews.create');
            Route::get('/bookings/{booking}/extension/{extension}', ManageExtension::class)->name('extensions.manage');
            Route::get('/income', HostIncome::class)->name('income');
            Route::get('/earnings', HostIncome::class)->name('earnings');

            Route::get('/properties', PropertyList::class)->name('properties.index');
            Route::get('/properties/create', PropertyForm::class)->name('properties.create');
            Route::get('/properties/{property}', PropertyShow::class)->name('properties.show');
            Route::get('/properties/{property}/edit', PropertyForm::class)->name('properties.edit');
            Route::get('/properties/{property}/rooms/create', RoomForm::class)->name('rooms.create');
            Route::get('/properties/{property}/rooms/{room}/edit', RoomForm::class)->name('rooms.edit');
            Route::get('/rooms/{room}/sleeping-places', SleepingPlaceList::class)->name('sleeping-places.index');
            Route::get('/rooms/{room}/sleeping-places/create', SleepingPlaceForm::class)->name('sleeping-places.create');
            Route::get('/rooms/{room}/sleeping-places/{sleepingPlace}/edit', SleepingPlaceForm::class)->name('sleeping-places.edit');
            Route::get('/rooms/{room}/beds/create', BedForm::class)->name('beds.create');
            Route::get('/rooms/{room}/beds/{bed}/edit', BedForm::class)->name('beds.edit');
        });
    });
