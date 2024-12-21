<?php

use AbdAlrzaq\Roles\Models\Role;
use Illuminate\Support\Facades\Route;

Route::get('roles', function (){
    return Role::all();
});