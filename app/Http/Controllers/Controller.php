<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesFileUploads;

abstract class Controller
{
    use HandlesFileUploads;
}
