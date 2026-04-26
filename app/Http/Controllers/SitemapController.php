<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $cars = Car::query()->publicInventory()->latest('updated_at')->get(['id', 'slug', 'updated_at']);

        $content = view('sitemap', compact('cars'))->render();

        return response($content, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
