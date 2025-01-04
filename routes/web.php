<?php

use App\Http\Controllers\MessengerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::group(['middleware' => 'auth'], function () {
    Route::get('messenger', [MessengerController::class, 'index'])->name('messenger');
    Route::post('profile',[UserProfileController::class, 'update'])->name('profile.update');
    //search route
    Route::get('messenger/search', [MessengerController::class, 'search'])->name('messenger.search');
    Route::get('messenger/id-info', [MessengerController::class, 'fetchIdInfo'])->name('messenger.id-info');
    //send message route
    Route::post('messenger/send-message', [MessengerController::class, 'sendMessage'])->name('messenger.send-message');
    Route::get('messenger/fetch-messages', [MessengerController::class, 'fetchMessages'])->name('messenger.fetch-messages');
    Route::get('messenger/fetch-contacts', [MessengerController::class, 'fetchContacts'])->name('messenger.fetch-contacts');
    Route::get('messenger/update-contact-item', [MessengerController::class, 'updateContactItem'])->name('messenger.update-contact-item');
    Route::post('messenger/make-seen', [MessengerController::class, 'makeSeen'])->name('messenger.make-seen');
    Route::post('messenger/favorite', [MessengerController::class, 'favorite'])->name('messenger.favorite');
    Route::get('messenger/fetch-favorites', [MessengerController::class, 'fetchFavorites'])->name('messenger.fetch-favorites');
    Route::DELETE('messenger/delete-message', [MessengerController::class, 'deleteMessage'])->name('messenger.delete-message');
    Route::DELETE('messenger/delete-online-status', [MessengerController::class, 'deleteOnlineStatus'])->name('messenger.delete-online-status');

});
