<?php

use Illuminate\Support\Facades\Blade;

function renderTable(string $attributes = ''): string
{
    return Blade::render(
        '<x-ui.table '.$attributes.'><thead><tr><th>Column</th></tr></thead><tbody><tr><td>Value</td></tr></tbody></x-ui.table>'
    );
}

it('renders the shared flowbite table shell', function () {
    $html = html_entity_decode(renderTable());

    expect($html)
        ->toContain('overflow-x-auto')
        ->toContain('rounded-lg border border-gray-200 bg-white shadow-sm')
        ->toContain('[&_thead]:bg-gray-50')
        ->toContain('[&_thead]:uppercase')
        ->toContain('[&_th]:font-semibold')
        ->toContain('[&_tbody_tr:hover]:bg-gray-50')
        ->toContain('[&_td]:py-4')
        ->toContain('min-w-[900px]')
        ->toContain('text-gray-500');
});

it('keeps tables horizontally scrollable on small screens for both widths', function () {
    expect(renderTable(':fit="true"'))->toContain('min-w-[720px]')
        ->and(renderTable())->toContain('min-w-[900px]')
        ->and(renderTable('min-width="min-w-[1200px]"'))->toContain('min-w-[1200px]');
});
