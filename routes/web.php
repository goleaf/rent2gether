<?php

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
use App\Livewire\Beds\ShowBed;
use App\Livewire\Booking\CancelBooking;
use App\Livewire\Booking\CreateBooking;
use App\Livewire\Booking\PaymentPage;
use App\Livewire\Bookings\CheckIn\GuestCheckInPage;
use App\Livewire\Bookings\CheckOut\GuestCheckOutPage;
use App\Livewire\Bookings\Create\BookingCreatePage;
use App\Livewire\Bookings\Requests\GuestBookingRequestPage;
use App\Livewire\Checkin\CheckIn;
use App\Livewire\Checkin\CheckOut;
use App\Livewire\Checkin\ProblemReport;
use App\Livewire\Compare\ComparePlaces;
use App\Livewire\Complaints\ComplaintDetail;
use App\Livewire\Complaints\CreateComplaint;
use App\Livewire\Extensions\ExtendStay;
use App\Livewire\Extensions\ManageExtension;
use App\Livewire\Favorites\FavoriteCollectionPage;
use App\Livewire\Host\BedForm;
use App\Livewire\Host\HostBookings;
use App\Livewire\Host\HostIncome;
use App\Livewire\Host\HostOnboardingPage;
use App\Livewire\Host\HostProfileEditPage;
use App\Livewire\Host\BookingRequests\HostBookingRequestsPage;
use App\Livewire\Host\Listings\CreateListingWizard;
use App\Livewire\Host\ManageBooking;
use App\Livewire\Host\Occupants\CurrentOccupantsPage;
use App\Livewire\Host\Properties\PropertyAccessStep;
use App\Livewire\Host\Properties\PropertyCompletionPanel;
use App\Livewire\Host\Properties\PropertyConditionStep;
use App\Livewire\Host\Properties\PropertyLocationStep;
use App\Livewire\Host\Properties\PropertyMainInfoStep;
use App\Livewire\Host\Properties\PropertyStructureStep;
use App\Livewire\Host\PropertyForm;
use App\Livewire\Host\PropertyList;
use App\Livewire\Host\PropertyShow;
use App\Livewire\Host\RoomForm;
use App\Livewire\Host\Rooms\RoomAccessStorageStep;
use App\Livewire\Host\Rooms\RoomComfortStep;
use App\Livewire\Host\Rooms\RoomCompletionPanel;
use App\Livewire\Host\Rooms\RoomConditionStep;
use App\Livewire\Host\Rooms\RoomLayoutStep;
use App\Livewire\Host\Rooms\RoomMainInfoStep;
use App\Livewire\Host\Rooms\RoomMediaStep;
use App\Livewire\Host\Rooms\RoomRulesStep;
use App\Livewire\Host\SleepingPlaceForm;
use App\Livewire\Host\SleepingPlaceList;
use App\Livewire\Host\SleepingPlaces\SleepingPlaceComfortStep;
use App\Livewire\Host\SleepingPlaces\SleepingPlaceCompletionPanel;
use App\Livewire\Host\SleepingPlaces\SleepingPlaceConditionStep;
use App\Livewire\Host\SleepingPlaces\SleepingPlaceMainInfoStep;
use App\Livewire\Host\SleepingPlaces\SleepingPlaceMediaStep;
use App\Livewire\Host\SleepingPlaces\SleepingPlacePhysicalStep;
use App\Livewire\Host\SleepingPlaces\SleepingPlacePositionStep;
use App\Livewire\Host\SleepingPlaces\SleepingPlacePricingStep;
use App\Livewire\Host\SleepingPlaces\SleepingPlaceStorageStep;
use App\Livewire\Messages\ChatWindow;
use App\Livewire\Notifications\NotificationsPage;
use App\Livewire\Pages\HealthPage;
use App\Livewire\Pages\HomePage;
use App\Livewire\Places\ShowSleepingPlace;
use App\Livewire\Profile\EditProfile;
use App\Livewire\Profile\ShowProfile;
use App\Livewire\Reviews\CreateReview;
use App\Livewire\SavedSearches\SavedSearchesPage;
use App\Livewire\SavedSearches\SavedSearchPage;
use App\Livewire\Search\SleepingPlaceSearch;
use App\Livewire\Shell\FavoritesPage;
use App\Livewire\Shell\HostCalendarPage;
use App\Livewire\Shell\HostHomePage;
use App\Livewire\Shell\HostListingsPage;
use App\Livewire\Shell\HostProfilePage;
use App\Livewire\Shell\MessagesPage;
use App\Livewire\Trips\BookingDetail;
use App\Livewire\Trips\CurrentStay;
use App\Livewire\Trips\TripList;
use App\Livewire\Waitlist\MyWaitlistPage;
use App\Livewire\Waitlist\WaitlistOfferPage;
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
            Route::get('/', SleepingPlaceSearch::class)->name('index');
        });

        Route::prefix('beds')->name('beds.')->group(function (): void {
            Route::get('/{bed}', ShowBed::class)->name('show');
        });

        Route::prefix('places')->name('places.')->group(function (): void {
            Route::get('/{sleepingPlace}', ShowSleepingPlace::class)->name('show');
        });
    });

Route::middleware([SetLocale::class, 'guest'])->prefix('auth')->name('auth.')->group(function (): void {
    Route::get('/login', LoginPage::class)->name('login');
    Route::get('/register', RegisterPage::class)->name('register');
    Route::get('/forgot-password', ForgotPasswordPage::class)->name('forgot-password');
});

Route::prefix('{locale}')
    ->whereIn('locale', config('localization.supported_locales'))
    ->middleware([SetLocale::class, 'auth'])
    ->group(function (): void {

        Route::prefix('bookings')->name('guest.bookings.')->group(function (): void {
            Route::get('/', TripList::class)->name('index');
            Route::get('/{booking}/payment', PaymentPage::class)->name('payment');
            Route::get('/{booking}/cancel', CancelBooking::class)->name('cancel');
            Route::get('/{booking}/check-in', GuestCheckInPage::class)->name('check-in');
            Route::get('/{booking}/check-out', GuestCheckOutPage::class)->name('check-out');
            Route::get('/{booking}', BookingDetail::class)->name('show');
        });

        Route::get('/trips', TripList::class)->name('trips.index');
        Route::get('/trips/current', CurrentStay::class)->name('trips.current');
        Route::get('/trips/{scope}', TripList::class)
            ->whereIn('scope', ['upcoming', 'past', 'cancelled'])
            ->name('trips.scope');

        Route::get('/beds/{bed}/book', CreateBooking::class)->name('beds.book');
        Route::get('/places/{sleepingPlace}/book', BookingCreatePage::class)->name('places.book');

        Route::prefix('booking-requests')->name('guest.booking-requests.')->group(function (): void {
            Route::get('/{request}', GuestBookingRequestPage::class)->name('show');
        });

        Route::prefix('profile')->name('profile.')->group(function (): void {
            Route::get('/', EditProfile::class)->name('index');
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
            Route::get('/collections/{favoriteCollection}', FavoriteCollectionPage::class)->name('collections.show');
        });

        Route::get('/bookings/{booking}/review', CreateReview::class)->name('reviews.create');
        Route::get('/bookings/{booking}/complaint', CreateComplaint::class)->name('complaints.create');
        Route::get('/complaints/{complaint}', ComplaintDetail::class)->name('complaints.show');
        Route::get('/bookings/{booking}/extend', ExtendStay::class)->name('bookings.extend');
        Route::get('/bookings/{booking}/checkin', CheckIn::class)->name('bookings.checkin');
        Route::get('/bookings/{booking}/checkin/problem', ProblemReport::class)->name('bookings.checkin.problem');
        Route::get('/bookings/{booking}/checkout', CheckOut::class)->name('bookings.checkout');

        Route::prefix('saved-searches')->name('saved-searches.')->group(function (): void {
            Route::get('/', SavedSearchesPage::class)->name('index');
            Route::get('/{savedSearch}', SavedSearchPage::class)->name('show');
        });
        Route::prefix('waitlist')->name('waitlist.')->group(function (): void {
            Route::get('/', MyWaitlistPage::class)->name('index');
            Route::get('/offers/{waitlistOffer}', WaitlistOfferPage::class)->name('offers.show');
        });
        Route::get('/compare', ComparePlaces::class)->name('compare.index');

        Route::prefix('host')->name('host.')->group(function (): void {
            Route::get('/', HostHomePage::class)->name('dashboard');
            Route::get('/listings', HostListingsPage::class)->name('listings.index');
            Route::get('/listings/wizard/{propertyId?}', CreateListingWizard::class)
                ->whereNumber('propertyId')
                ->name('listings.create');
            Route::get('/listings/{scope}', HostListingsPage::class)
                ->whereIn('scope', ['drafts', 'hidden'])
                ->name('listings.scope');
            Route::get('/calendar', HostCalendarPage::class)->name('calendar');
            Route::get('/occupants', CurrentOccupantsPage::class)->name('occupants.index');
            Route::get('/requests', HostBookingRequestsPage::class)->name('requests.index');
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
            Route::prefix('properties/{property}/extended')->name('properties.extended.')->group(function (): void {
                Route::get('/main', PropertyMainInfoStep::class)->name('main');
                Route::get('/structure', PropertyStructureStep::class)->name('structure');
                Route::get('/location', PropertyLocationStep::class)->name('location');
                Route::get('/condition', PropertyConditionStep::class)->name('condition');
                Route::get('/access', PropertyAccessStep::class)->name('access');
                Route::get('/completion', PropertyCompletionPanel::class)->name('completion');
            });
            Route::get('/properties/{property}/rooms/create', RoomForm::class)->name('rooms.create');
            Route::get('/properties/{property}/rooms/{room}/edit', RoomForm::class)->name('rooms.edit');
            Route::prefix('rooms/{room}/extended')->name('rooms.extended.')->group(function (): void {
                Route::get('/main', RoomMainInfoStep::class)->name('main');
                Route::get('/layout', RoomLayoutStep::class)->name('layout');
                Route::get('/comfort', RoomComfortStep::class)->name('comfort');
                Route::get('/access-storage', RoomAccessStorageStep::class)->name('access-storage');
                Route::get('/condition', RoomConditionStep::class)->name('condition');
                Route::get('/rules', RoomRulesStep::class)->name('rules');
                Route::get('/media', RoomMediaStep::class)->name('media');
                Route::get('/completion', RoomCompletionPanel::class)->name('completion');
            });
            Route::get('/rooms/{room}/sleeping-places', SleepingPlaceList::class)->name('sleeping-places.index');
            Route::get('/rooms/{room}/sleeping-places/create', SleepingPlaceForm::class)->name('sleeping-places.create');
            Route::get('/rooms/{room}/sleeping-places/{sleepingPlace}/edit', SleepingPlaceForm::class)->name('sleeping-places.edit');
            Route::prefix('sleeping-places/{sleepingPlace}/extended')->name('sleeping-places.extended.')->group(function (): void {
                Route::get('/main', SleepingPlaceMainInfoStep::class)->name('main');
                Route::get('/physical', SleepingPlacePhysicalStep::class)->name('physical');
                Route::get('/comfort', SleepingPlaceComfortStep::class)->name('comfort');
                Route::get('/storage', SleepingPlaceStorageStep::class)->name('storage');
                Route::get('/position', SleepingPlacePositionStep::class)->name('position');
                Route::get('/pricing', SleepingPlacePricingStep::class)->name('pricing');
                Route::get('/condition', SleepingPlaceConditionStep::class)->name('condition');
                Route::get('/media', SleepingPlaceMediaStep::class)->name('media');
                Route::get('/completion', SleepingPlaceCompletionPanel::class)->name('completion');
            });
            Route::get('/rooms/{room}/beds/create', BedForm::class)->name('beds.create');
            Route::get('/rooms/{room}/beds/{bed}/edit', BedForm::class)->name('beds.edit');
        });
    });
