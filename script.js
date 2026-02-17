document.addEventListener('DOMContentLoaded', function() {

    // 1. Hero Slider
    new Swiper(".heroSwiper", {
        effect: "slide",
        loop: true,
        speed: 800,
        autoplay: { delay: 4000, disableOnInteraction: false },
        navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" },
        pagination: { el: ".swiper-pagination", clickable: true },
    });

    // 2. Category Continuous Marquee
new Swiper(".catSwiper", {
    loop: true,
    spaceBetween: 20,
    speed: 4000, // Adjust speed for total smoothness (higher = slower)
    autoplay: { 
        delay: 0, // 0 delay for continuous motion
        disableOnInteraction: false,
        pauseOnMouseEnter: true 
    },
    freeMode: {
        enabled: true,
        momentum: false,
    },
    breakpoints: {
        0: { slidesPerView: 2 },
        768: { slidesPerView: 4 },
        1400: { slidesPerView: 7 }
    }
});

    // 3. Service Cards Slider
    new Swiper(".serviceSwiper", {
        slidesPerView: 1,
        spaceBetween: 25,
        pagination: { el: ".swiper-pagination", clickable: true },
        breakpoints: {
            768: { slidesPerView: 2 },
            1100: { slidesPerView: 3 }
        }
    });

    // 4. Offers Slider
    new Swiper(".offerSwiper", {
        slidesPerView: 1,
        spaceBetween: 20,
        loop: true,
        autoplay: { delay: 3500 },
        pagination: { el: ".swiper-pagination", clickable: true },
        breakpoints: {
            640: { slidesPerView: 2 },
            1200: { slidesPerView: 4 }
        }
    });

    // 5. Brand Infinite Marquee (Vanilla JS)
    const track1 = document.getElementById('track1');
    if (track1) {
        let xPos1 = 0;
        const speed1 = 0.8;
        let isPaused1 = false;

        function animateBrands() {
            if (!isPaused1) {
                xPos1 -= speed1;
                if (Math.abs(xPos1) >= track1.scrollWidth / 2) { xPos1 = 0; }
                track1.style.transform = `translateX(${xPos1}px)`;
            }
            requestAnimationFrame(animateBrands);
        }

        track1.parentElement.addEventListener('mouseenter', () => isPaused1 = true);
        track1.parentElement.addEventListener('mouseleave', () => isPaused1 = false);
        animateBrands();
    }
});

// Modal Control Function
function toggleModal(id, show) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.style.display = show ? 'flex' : 'none';
        document.body.style.overflow = show ? 'hidden' : 'auto';
    }
}

// Close Modal on outside click
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}
