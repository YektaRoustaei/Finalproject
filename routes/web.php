<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;

Route::get('/send-test-email', function () {
    Mail::to('test@example.com')->send(new TestMail());
    return 'Test email sent!';
});
