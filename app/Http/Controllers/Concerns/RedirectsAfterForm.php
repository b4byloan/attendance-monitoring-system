<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait RedirectsAfterForm
{
    /**
     * Return to the page the form was opened from (for example a modal on an
     * index page), while only allowing local URLs.
     */
    protected function redirectAfterForm(Request $request, string $fallbackUrl): RedirectResponse
    {
        $target = trim((string) $request->input('redirect_to'));
        $base = rtrim(url('/'), '/');

        $url = $target !== '' && ($target === $base || str_starts_with($target, $base.'/'))
            ? $target
            : $fallbackUrl;

        return redirect()->to($url);
    }
}
