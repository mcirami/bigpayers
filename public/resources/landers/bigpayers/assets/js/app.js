("use strict");

document.addEventListener("DOMContentLoaded", function (event) {
    let splide = new Splide(".splide", {
        type: "loop",
        perPage: 14,
        arrows: false,
        pagination: false,
        speed: 860,
        autoScroll: {
            speed: 0.5,
            pauseOnHover: false,
        },
        lazyLoad: "nearby",
        drag: "free",
        focus: "center",
        gap: "3em",
        mediaQuery: "max",
        breakpoints: {
            992: {
                perPage: 10,
                autoScroll: {
                    speed: 0.5,
                },
            },
            767: {
                perPage: 8,
                autoScroll: {
                    speed: 0.5,
                },
            },
            550: {
                perPage: 6,
                autoScroll: {
                    speed: 0.5,
                },
            },
        },
    });

    splide.mount(window.splide.Extensions);
});
