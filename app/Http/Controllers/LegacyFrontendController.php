<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class LegacyFrontendController extends Controller
{
    public function show(?string $page = 'index'): View|Response
    {
        $page = $this->normalizePage($page);
        $view = 'frontend.pages.' . $page;

        if (! view()->exists($view)) {
            if (view()->exists('frontend.pages.404')) {
                return response()->view('frontend.pages.404', [], 404);
            }

            abort(404);
        }

        return view($view);
    }

    private function normalizePage(?string $page): string
    {
        $page = trim((string) $page, '/');
        $page = $page === '' ? 'index' : $page;
        $page = preg_replace('/\.(html|php)$/i', '', $page) ?: 'index';

        return $page === 'welcome.blade' ? 'index' : $page;
    }
}
