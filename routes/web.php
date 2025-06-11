<?php

use App\Http\Controllers\MainController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/contacts', [MainController::class, 'indexContacts']);
Route::get('/fields', [MainController::class, 'indexFields']);
Route::get('/fields/delete', [MainController::class, 'deleteField']);
Route::get('/time', [MainController::class, 'testTime']);
Route::get('/users', [MainController::class, 'indexAccountUsers']);
Route::get('/contacts/create', [MainController::class, 'contactsCreate'])->name('contacts.create');
Route::post('/contacts', [MainController::class, 'storeContact'])->name('contacts.store');
Route::get('/refresh', [MainController::class, 'refresh']);
Route::get('/testProducts', [MainController::class, 'testProducts']);
