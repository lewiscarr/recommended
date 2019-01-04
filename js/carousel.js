$(document).ready(function() {

    $('div#rc_carousel').slick({
        infinite: true,
        centerMode: false,
        slidesToShow: 4,
        slidesToScroll: 1,
        centerPadding: '0px',
        responsive: [
        {
            breakpoint: 1082,
            settings: {
                infinite: true,
                centerMode: false,
                slidesToShow: 3,
                slidesToScroll: 1,
                centerPadding: '0px',
                adaptiveHeight: false
            }
        },
        {
            breakpoint: 768,
            settings: {
                infinite: true,
                centerMode: false,
                slidesToShow: 2,
                slidesToScroll: 1,
                centerPadding: '0px',
                adaptiveHeight: false
            }
        },
        {
            breakpoint: 480,
            settings: {
                infinite: true,
                centerMode: false,
                slidesToShow: 1,
                slidesToScroll: 1,
                centerPadding: '0px',
                adaptiveHeight: false
            }
        }
        ]
    });

});
