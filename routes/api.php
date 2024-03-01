<?php

use App\Http\Controllers\api\agora\Call;
use App\Http\Controllers\api\apple_pay\InAppSubscriptions;
use App\Http\Controllers\api\bank_details\BankDetails;
use App\Http\Controllers\api\clear_image\ClearImage;
use App\Http\Controllers\api\contact_details\ContactDetails;
use App\Http\Controllers\api\contact_us\ContactUs;
use App\Http\Controllers\api\delete_account\DeleteUser;
use App\Http\Controllers\api\parents\Auth;
use App\Http\Controllers\api\parents\BlockOrReportUser as ParentsBlockOrReportUser;
use App\Http\Controllers\api\parents\Chat as ParentsChat;
use App\Http\Controllers\api\parents\InstantMatch;
use App\Http\Controllers\api\parents\Matches as ParentsMatches;
use App\Http\Controllers\api\parents\Notifications as ParentsNotifications;
use App\Http\Controllers\api\parents\Profile as ParentsProfile;
use App\Http\Controllers\api\parents\Suggestions as ParentsSuggestions;
use App\Http\Controllers\api\parents\Swipes as ParentsSwipes;
use App\Http\Controllers\api\quotes\IslamicQuotes;
use App\Http\Controllers\api\reset_profile_search\ResetProfileSearch;
use App\Http\Controllers\api\singletons\Auth as SingletonsAuth;
use App\Http\Controllers\api\singletons\BlockOrReportUser;
use App\Http\Controllers\api\singletons\Chat;
use App\Http\Controllers\api\singletons\InstantMatch as SingletonsInstantMatch;
use App\Http\Controllers\api\singletons\Matches as SingletonsMatches;
use App\Http\Controllers\api\singletons\Notifications;
use App\Http\Controllers\api\singletons\Profile;
use App\Http\Controllers\api\singletons\Suggestions;
use App\Http\Controllers\api\singletons\Swipes as SingletonsSwipes;
use App\Http\Controllers\api\static_pages\StaticPages;
use App\Http\Controllers\api\stripe\StripeSubscription;
use App\Http\Controllers\api\subscriptions\SubscriptionPlans;
use App\Http\Controllers\api\version\VersionController;
use App\Http\Controllers\api\web_pages\WebPages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Singletons
Route::prefix('singleton')->group(function () {

    // Singleton Auth
    Route::post('register' , [SingletonsAuth::class, 'index']);
    Route::post('social-register' , [SingletonsAuth::class, 'socialRegistration']);
    Route::post('validateEmail' , [SingletonsAuth::class, 'validateEmail']);
    Route::post('resendRegisterOTP' , [SingletonsAuth::class, 'resendRegisterOTP']);

    Route::post('login' , [SingletonsAuth::class, 'login']);
    Route::post('social-login' , [SingletonsAuth::class, 'socialLogin']);

    Route::post('forgetPassword' , [SingletonsAuth::class, 'forgetPassword']);
    Route::post('validateForgetPassword' , [SingletonsAuth::class, 'validateForgetPassword']);
    Route::get('reset-password' , [SingletonsAuth::class, 'ResetPassword']);
    Route::post('set-new-password' , [SingletonsAuth::class, 'setNewPassword']);

    // Singleton Profile
    Route::post('get-profile' , [Profile::class, 'index']);
    Route::post('update-profile' , [Profile::class, 'updateProfile']);
    Route::post('update-location' , [Profile::class, 'updatecurrentlocation']);
    Route::post('upload-photos' , [Profile::class, 'uploadPhotos']);
    Route::post('update-blurred-status' , [Profile::class, 'updateBlurredStatus']);
    Route::post('remove-photo' , [Profile::class, 'deleteUploadedPhoto']);
    Route::post('get-access-details' , [Profile::class, 'getAccessDetails']);
    Route::post('busy' , [Profile::class, 'chatInProgress']);

    // Singleton Categories
    Route::post('get-categories' , [Suggestions::class, 'index']);
    Route::post('add-categories' , [Suggestions::class, 'addCategory']);

    // Profile Suggestions
    Route::post('get-sugestions' , [Suggestions::class, 'suggestions']);

    // Block and Report User
    Route::post('block-user' , [BlockOrReportUser::class, 'index']);
    Route::post('report-user' , [BlockOrReportUser::class, 'reportUser']);

    // Matches
    Route::post('un-match-user' , [SingletonsMatches::class, 'index']);
    Route::post('re-match-user' , [SingletonsMatches::class, 'reMatch']);
    Route::post('my-matches' , [SingletonsMatches::class, 'myMatches']);
    Route::post('reffered-matches' , [SingletonsMatches::class, 'RefferedMatches']);
    Route::post('recieved-matches' , [SingletonsMatches::class, 'RecievedMatches']);
    Route::post('mutual-matches' , [SingletonsMatches::class, 'MutualMatches']);
    Route::post('match-found' , [SingletonsMatches::class, 'matchFound']);

    // Instant Match Request
    Route::post('send-request' , [SingletonsInstantMatch::class, 'sendInstantRequest']);
    Route::post('change-request-status' , [SingletonsInstantMatch::class, 'changeRequestStatus']);
    Route::post('request-list' , [SingletonsInstantMatch::class, 'requestList']);

    // Swipes
    Route::post('swipe' , [SingletonsSwipes::class, 'index']);

    // Chats
    Route::post('send-message' , [Chat::class, 'index']);
    Route::post('messaged-users-list' , [Chat::class, 'messagedUsers']);
    Route::post('chat-history' , [Chat::class, 'chatHistory']);
    Route::post('close-chat' , [Chat::class, 'closeChat']);
    Route::post('invite-parent' , [Chat::class, 'inviteParent']);
    Route::post('delete-chat' , [Chat::class, 'deleteChat']);

    // Notifications
    Route::post('get-notifications' , [Notifications::class, 'index']);
});

// Parents
Route::prefix('parent')->group(function () {

    // Parent Auth
    Route::post('register' , [Auth::class, 'index']);
    Route::post('social-register' , [Auth::class, 'socialRegistration']);
    Route::post('validateEmail' , [Auth::class, 'validateEmail']);
    Route::post('resendRegisterOTP' , [Auth::class, 'resendRegisterOTP']);

    Route::post('login' , [Auth::class, 'login']);
    Route::post('social-login' , [Auth::class, 'socialLogin']);

    Route::post('forgetPassword' , [Auth::class, 'forgetPassword']);
    Route::post('validateForgetPassword' , [Auth::class, 'validateForgetPassword']);
    Route::get('reset-password' , [Auth::class, 'ResetPassword']);
    Route::post('set-new-password' , [Auth::class, 'setNewPassword']);

    // Parent Profile
    Route::post('get-profile' , [ParentsProfile::class, 'index']);
    Route::post('update-profile' , [ParentsProfile::class, 'updateProfile']);
    Route::post('update-location' , [ParentsProfile::class, 'updatecurrentlocation']);
    Route::post('search-child' , [ParentsProfile::class, 'searchChild']);
    Route::post('send-access-request' , [ParentsProfile::class, 'sendAccessRequest']);
    Route::post('verify-access-code' , [ParentsProfile::class, 'verifyAccessRequest']);
    Route::post('get-linked-profiles' , [ParentsProfile::class, 'getLinkedProfiles']);
    Route::post('get-child-profile' , [ParentsProfile::class, 'getChildProfile']);

    // Profile Suggestions
    Route::post('add-categories' , [ParentsSuggestions::class, 'addCategory']);
    Route::post('get-categories' , [ParentsSuggestions::class, 'index']);
    Route::post('get-sugestions' , [ParentsSuggestions::class, 'suggestions']);

    // Block and Report User
    Route::post('block-user' , [ParentsBlockOrReportUser::class, 'index']);
    Route::post('report-user' , [ParentsBlockOrReportUser::class, 'reportUser']);

    // Matches
    Route::post('un-match-user' , [ParentsMatches::class, 'index']);
    Route::post('re-match-user' , [ParentsMatches::class, 'reMatch']);
    Route::post('my-matches' , [ParentsMatches::class, 'myMatches']);
    Route::post('reffered-matches' , [ParentsMatches::class, 'RefferedMatches']);
    Route::post('recieved-matches' , [ParentsMatches::class, 'RecievedMatches']);
    Route::post('mutual-matches' , [ParentsMatches::class, 'MutualMatches']);

    // Instant Match Request
    Route::post('send-request' , [InstantMatch::class, 'sendInstantRequest']);
    Route::post('change-request-status' , [InstantMatch::class, 'changeRequestStatus']);
    Route::post('request-list' , [InstantMatch::class, 'requestList']);

    // Swipes
    Route::post('swipe' , [ParentsSwipes::class, 'index']);

    // Chats
    Route::post('send-message' , [ParentsChat::class, 'index']);
    Route::post('messaged-users-list' , [ParentsChat::class, 'messagedUsers']);
    Route::post('chat-history' , [ParentsChat::class, 'chatHistory']);
    Route::post('invite-child' , [ParentsChat::class, 'inviteChild']);
    Route::post('delete-chat' , [ParentsChat::class, 'deleteChat']);

    // Notifications
    Route::post('get-notifications' , [ParentsNotifications::class, 'index']);
});

// Static Pages
Route::prefix('static-pages')->group(function () {
    Route::post('get-page' , [StaticPages::class, 'index']);
});

// Web Pages
Route::prefix('web-pages')->group(function () {
    Route::post('get-page' , [WebPages::class, 'index']);
});

// Contact Details
Route::prefix('contact-details')->group(function () {
    Route::post('get-contact-details' , [ContactDetails::class, 'index']);
});

// Subscription Plans
Route::prefix('subscriptions')->group(function () {
    Route::post('get-subscription-plans' , [SubscriptionPlans::class, 'index']);
    Route::post('get-active-subscription' , [SubscriptionPlans::class, 'activeSubscription']);
    Route::post('premium' , [SubscriptionPlans::class, 'isPremium']);
    Route::post('non-premium-singletons' , [SubscriptionPlans::class, 'nonPremiumSingletons']);
});

// Islamic Quotes
Route::prefix('quotes')->group(function () {
    Route::post('get-islamic-quotes' , [IslamicQuotes::class, 'index']);
});

// Contact Us
Route::prefix('contact-us')->group(function () {
    Route::post('add-contact-us' , [ContactUs::class, 'index']);
});

// Add Bank Details
Route::prefix('bank-details')->group(function () {
    Route::post('get-bank-details' , [BankDetails::class, 'index']);
    Route::post('add-bank-details' , [BankDetails::class, 'addCard']);
    Route::post('delete-bank-details' , [BankDetails::class, 'deleteCard']);
});

// Delete User Account
Route::prefix('delete-account')->group(function () {
    Route::post('delete-account' , [DeleteUser::class, 'index']);
});

// Reset Profile Search
Route::prefix('reset-profile-search')->group(function () {
    Route::post('reset' , [ResetProfileSearch::class, 'index']);
});

// Stripe Subscription
Route::prefix('stripe')->group(function () {
    Route::post('subscribe' , [StripeSubscription::class, 'index']);
    Route::any('success' , [StripeSubscription::class, 'paymentSuccess']);
    Route::any('fail' , [StripeSubscription::class, 'paymentFail']);
    Route::post('webhook' , [StripeSubscription::class, 'webhookHandler']);
});

// In-App Subscription
Route::prefix('apple')->group(function () {
    Route::post('subscribe' , [InAppSubscriptions::class, 'index']);
    Route::post('success' , [InAppSubscriptions::class, 'paymentSuccess']);
    Route::post('fail' , [InAppSubscriptions::class, 'paymentFail']);
    Route::post('update' , [InAppSubscriptions::class, 'updateSubscription']);
    Route::post('cancel' , [InAppSubscriptions::class, 'cancelSubscription']);
});

// Agora
Route::prefix('agora')->group(function () {
    Route::post('generate-token' , [Call::class, 'index']);
    Route::post('save' , [Call::class, 'callHistory']);
    Route::post('reject' , [Call::class, 'rejectCall']);
});

// App Version
Route::post('app/version' , [VersionController::class, 'index']);

// Clear Image
Route::post('clear-image' , [ClearImage::class, 'clearImage']);