<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Page;

class PageController extends Controller
{
    public function faq()
    {
        return view('pages.faq');
    }

    public function terms()
    {
        return view('pages.terms');
    }

    public function refund()
    {
        return view('pages.refund');
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function flow()
    {
        return view('pages.flow-bisnis');
    }

    public function show($slug)
    {
        if (in_array($slug, ['flow', 'flow-bisnis', 'business-flow'])) {
            return view('pages.flow-bisnis');
        }

        if (in_array($slug, ['faq', 'help-center'])) {
            return view('pages.faq');
        }

        if (in_array($slug, ['syarat-ketentuan', 'terms-of-service'])) {
            return view('pages.terms');
        }

        if (in_array($slug, ['refund-policy'])) {
            return view('pages.refund');
        }

        if (in_array($slug, ['kontak', 'contact-us'])) {
            return view('pages.contact');
        }

        $titles = [
            'about' => 'About Us',
            'pricing' => 'Pricing',
            'help-center' => 'Help Center',
            'privacy-policy' => 'Privacy Policy',
            'terms-of-service' => 'Terms of Service',
            'contact-us' => 'Contact Us',
        ];

        $page = Page::firstOrCreate(
            ['slug' => $slug],
            [
                'title' => $titles[$slug] ?? ucfirst(str_replace('-', ' ', $slug)),
                'content' => '
                    <div class="prose prose-invert max-w-none prose-orange">
                        <p class="text-stone-400 leading-relaxed mb-6">Selamat datang di halaman ' . ($titles[$slug] ?? ucfirst(str_replace('-', ' ', $slug))) . ' IdenTix.</p>
                    </div>
                '
            ]
        );

        return view('pages.show', compact('page'));
    }
}
