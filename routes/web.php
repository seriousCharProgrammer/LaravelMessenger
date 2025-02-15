<?php

use App\Http\Controllers\BlockContactController;
use App\Http\Controllers\cancelCallController;
use App\Http\Controllers\MessengerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\videoCall;
use App\Http\Controllers\VoiceMessageController;
use App\Http\Controllers\WebRtcController;
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
    Route::get('messenger/fetch-online-status', [MessengerController::class, 'fetchOnlineStatus'])->name('messenger.fetch-online-status');
    Route::post('messenger/send-voice-message', [VoiceMessageController::class, 'sendVoiceMessage'])->name('messenger.send-voice-message');
    Route::get('messenger/fetch-voice-messages', [VoiceMessageController::class, 'fetchVoiceMessages'])->name('messenger.fetch-voice-messages');
    Route::post('messenger/video-call',[videoCall::class,'index'])->name('messenger.video-call');
    Route::post('messenger/cancel-call',[cancelCallController::class,'index'])->name('messenger.cancel-call');
    Route::post('messenger/block-contact',[BlockContactController::class,'blockContact'])->name('messenger.block-contact');
    Route::post('messenger/unblock-contact',[BlockContactController::class,'unblockContact'])->name('messenger.unblock-contact');
    Route::get('messenger/fetch-blocked-contact',[BlockContactController::class,'fetchBlockContact'])->name('messenger.fetch-blocked-contact');
    Route::post('messenger/end-call',[videoCall::class,'endCall'])->name('messenger.end-call');
    Route::post('messenger/signal',[WebRtcController::class,'handleSignal'])->name('messenger.signal');
});
