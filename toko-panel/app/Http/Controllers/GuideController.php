<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Contracts\View\View;

class GuideController extends Controller
{
    public function __invoke(): View
    {
        return view('guide', [
            'plans' => Plan::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }
}
