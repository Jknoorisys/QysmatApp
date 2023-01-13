<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\admin\ContactDetails;
use App\Http\Controllers\admin\Dashboard;
use App\Http\Controllers\admin\DeletedUsers;
use App\Http\Controllers\admin\FAQS;
use App\Http\Controllers\admin\Notifications;
use App\Http\Controllers\admin\ParentsController;
use App\Http\Controllers\admin\Quotes;
use App\Http\Controllers\admin\ReportedUsers;
use App\Http\Controllers\admin\Singletons;
use App\Http\Controllers\admin\StaticPages;
use App\Http\Controllers\admin\Subscriptions;
use App\Http\Controllers\admin\Transactions;
use App\Http\Controllers\admin\WebPages;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Admin
Route::get('/' , [Admin::class,'index'])->name('/');
Route::get('lang/{lang}' , ['as'=>'lang.switch', 'uses'=>'App\Http\Controllers\Admin@setLanguage']);
Route::post('login' , [Admin::class,'login'])->name('login');
Route::any('logout' , [Admin::class,'logout'])->name('logout');
Route::any('dashboard' , [Dashboard::class,'index'])->middleware('isLoggedIn')->name('dashboard');
Route::get('changePassword' , [Dashboard::class,'changePassword'])->middleware('isLoggedIn')->name('changePassword');
Route::post('changePasswordFun' , [Dashboard::class,'changePasswordFun'])->middleware('isLoggedIn')->name('changePasswordFun');
Route::get('readNotifications' , [Dashboard::class,'readNotifications'])->middleware('isLoggedIn')->name('readNotifications');

Route::post('set-new-password' , [Admin::class, 'setUserNewPassword'])->name('set-new-password');

// Manage Singletons
Route::any('sigletons' , [Singletons::class, 'index'])->middleware('isLoggedIn')->name('sigletons');
Route::post('viewSingleton' , [Singletons::class, 'viewSingleton'])->middleware('isLoggedIn')->name('viewSingleton');
Route::post('verifySingleton' , [Singletons::class, 'verifySingleton'])->middleware('isLoggedIn')->name('verifySingleton');
Route::post('changeStatus' , [Singletons::class, 'changeStatus'])->middleware('isLoggedIn')->name('changeStatus');
Route::any('deleteSingleton' , [Singletons::class, 'deleteSingleton'])->middleware('isLoggedIn')->name('deleteSingleton');

// Manage Parents
Route::any('parents' , [ParentsController::class, 'index'])->middleware('isLoggedIn')->name('parents');
Route::post('viewParent' , [ParentsController::class, 'viewParent'])->middleware('isLoggedIn')->name('viewParent');
Route::post('verifyParent' , [ParentsController::class, 'verifyParent'])->middleware('isLoggedIn')->name('verifyParent');
Route::post('changeParentStatus' , [ParentsController::class, 'changeParentStatus'])->middleware('isLoggedIn')->name('changeParentStatus');
Route::any('deleteParent' , [ParentsController::class, 'deleteParent'])->middleware('isLoggedIn')->name('deleteParent');

// Manage Transactions
Route::any('transactions' , [Transactions::class, 'index'])->middleware('isLoggedIn')->name('transactions');
Route::post('viewTransaction' , [Transactions::class, 'viewTransaction'])->middleware('isLoggedIn')->name('viewTransaction');

//Manage Subscriptions
Route::any('subscriptions' , [Subscriptions::class, 'index'])->middleware('isLoggedIn')->name('subscriptions');
Route::post('changeSubscriptionStatus' , [Subscriptions::class, 'changeSubscriptionStatus'])->middleware('isLoggedIn')->name('changeSubscriptionStatus');
Route::post('updatePrice' , [Subscriptions::class, 'updatePrice'])->middleware('isLoggedIn')->name('updatePrice');
Route::post('updatePriceFun' , [Subscriptions::class, 'updatePriceFun'])->middleware('isLoggedIn')->name('updatePriceFun');

// Manage Contact Details
Route::any('contact_details' , [ContactDetails::class, 'index'])->middleware('isLoggedIn')->name('contact_details');
Route::post('changeContactStatus' , [ContactDetails::class, 'changeContactStatus'])->middleware('isLoggedIn')->name('changeContactStatus');
Route::post('deleteContact' , [ContactDetails::class, 'deleteContact'])->middleware('isLoggedIn')->name('deleteContact');
Route::any('addContact' , [ContactDetails::class, 'addContact'])->middleware('isLoggedIn')->name('addContact');
Route::post('addContactFun' , [ContactDetails::class, 'addContactFun'])->middleware('isLoggedIn')->name('addContactFun');
Route::post('updateContact' , [ContactDetails::class, 'updateContact'])->middleware('isLoggedIn')->name('updateContact');
Route::post('updateContactFun' , [ContactDetails::class, 'updateContactFun'])->middleware('isLoggedIn')->name('updateContactFun');

// Manage Static Pages
Route::any('static_pages' , [StaticPages::class, 'index'])->middleware('isLoggedIn')->name('static_pages');
Route::post('changePageStatus' , [StaticPages::class, 'changePageStatus'])->middleware('isLoggedIn')->name('changePageStatus');
Route::post('deletePage' , [StaticPages::class, 'deletePage'])->middleware('isLoggedIn')->name('deletePage');
Route::any('addPage' , [StaticPages::class, 'addPage'])->middleware('isLoggedIn')->name('addPage');
Route::post('addPageFun' , [StaticPages::class, 'addPageFun'])->middleware('isLoggedIn')->name('addPageFun');
Route::post('updatePage' , [StaticPages::class, 'updatePage'])->middleware('isLoggedIn')->name('updatePage');
Route::post('updatePageFun' , [StaticPages::class, 'updatePageFun'])->middleware('isLoggedIn')->name('updatePageFun');

// Manage Web Pages
Route::any('web_pages' , [WebPages::class, 'index'])->middleware('isLoggedIn')->name('web_pages');
Route::post('changeWebPageStatus' , [WebPages::class, 'changeWebPageStatus'])->middleware('isLoggedIn')->name('changeWebPageStatus');
Route::post('deleteWebPage' , [WebPages::class, 'deleteWebPage'])->middleware('isLoggedIn')->name('deleteWebPage');
Route::any('addWebPage' , [WebPages::class, 'addWebPage'])->middleware('isLoggedIn')->name('addWebPage');
Route::post('addWebPageFun' , [WebPages::class, 'addWebPageFun'])->middleware('isLoggedIn')->name('addWebPageFun');
Route::post('updateWebPage' , [WebPages::class, 'updateWebPage'])->middleware('isLoggedIn')->name('updateWebPage');
Route::post('updateWebPageFun' , [WebPages::class, 'updateWebPageFun'])->middleware('isLoggedIn')->name('updateWebPageFun');

// Manage FAQs
Route::any('faqs' , [FAQS::class, 'index'])->middleware('isLoggedIn')->name('faqs');
Route::post('changeFAQStatus' , [FAQS::class, 'changeFAQStatus'])->middleware('isLoggedIn')->name('changeFAQStatus');
Route::post('deletePage' , [FAQS::class, 'deletePage'])->middleware('isLoggedIn')->name('deletePage');
Route::any('addFAQ' , [FAQS::class, 'addFAQ'])->middleware('isLoggedIn')->name('addFAQ');
Route::post('addFAQFun' , [FAQS::class, 'addFAQFun'])->middleware('isLoggedIn')->name('addFAQFun');
Route::post('updateFAQ' , [FAQS::class, 'updateFAQ'])->middleware('isLoggedIn')->name('updateFAQ');
Route::post('updateFAQFun' , [FAQS::class, 'updateFAQFun'])->middleware('isLoggedIn')->name('updateFAQFun');
Route::any('deleteFAQ' , [FAQS::class, 'deleteFAQ'])->middleware('isLoggedIn')->name('deleteFAQ');

// Manage Quotes
Route::any('quotes' , [Quotes::class, 'index'])->middleware('isLoggedIn')->name('quotes');
Route::post('changeQuoteStatus' , [Quotes::class, 'changeQuoteStatus'])->middleware('isLoggedIn')->name('changeQuoteStatus');
Route::post('deleteQuote' , [Quotes::class, 'deleteQuote'])->middleware('isLoggedIn')->name('deleteQuote');
Route::any('addQuote' , [Quotes::class, 'addQuote'])->middleware('isLoggedIn')->name('addQuote');
Route::post('addQuoteFun' , [Quotes::class, 'addQuoteFun'])->middleware('isLoggedIn')->name('addQuoteFun');
Route::post('updateQuote' , [Quotes::class, 'updateQuote'])->middleware('isLoggedIn')->name('updateQuote');
Route::post('updateQuoteFun' , [Quotes::class, 'updateQuoteFun'])->middleware('isLoggedIn')->name('updateQuoteFun');

// Manage Reported Users
Route::any('reported-users' , [ReportedUsers::class, 'index'])->middleware('isLoggedIn')->name('reported-users');

// Manage Deleted Users
Route::any('deleted-users' , [DeletedUsers::class, 'index'])->middleware('isLoggedIn')->name('deleted-users');

// Manage Notifications
Route::any('notifications' , [Notifications::class, 'index'])->middleware('isLoggedIn')->name('notifications');
