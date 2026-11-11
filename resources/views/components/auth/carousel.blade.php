@php
    $slides = [
        ['src' => 'images/auth/campus.jpg', 'alt' => 'School campus and classroom building'],
        ['src' => 'images/auth/students.jpg', 'alt' => 'Students learning together in class'],
        ['src' => 'images/auth/learning.jpg', 'alt' => 'Books and learning materials'],
    ];
@endphp

{{-- Fills its container on desktop, and the whole viewport on mobile. --}}
<div class="absolute inset-0" data-auth-carousel aria-hidden="true">
    @foreach ($slides as $index => $slide)
        <img src="{{ asset($slide['src']) }}"
             alt="{{ $slide['alt'] }}"
             @if ($index > 0) loading="lazy" @endif
             class="absolute inset-0 h-full w-full object-cover transition-opacity duration-1000 ease-in-out {{ $index === 0 ? 'opacity-100' : 'opacity-0' }}"
             data-carousel-slide>
    @endforeach

    <div class="absolute inset-0 bg-emerald-950/70 lg:bg-emerald-950/45"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-emerald-950 via-emerald-950/25 to-transparent"></div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const carousel = document.querySelector('[data-auth-carousel]');

                if (!carousel) {
                    return;
                }

                const slides = Array.from(carousel.querySelectorAll('[data-carousel-slide]'));

                if (slides.length < 2 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    return;
                }

                let current = 0;

                window.setInterval(function () {
                    slides[current].classList.replace('opacity-100', 'opacity-0');
                    current = (current + 1) % slides.length;
                    slides[current].classList.replace('opacity-0', 'opacity-100');
                }, 5000);
            });
        </script>
    @endpush
@endonce
