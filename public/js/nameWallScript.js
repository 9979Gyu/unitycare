$(document).ready(function() {
    // Select all name-item elements
    const $nameItems = $('.name-item');

    // Function to pause scrolling
    function pauseScroll() {
        $('#name-wall').addClass('paused');
    }

    // Function to resume scrolling
    function resumeScroll() {
        $('#name-wall').removeClass('paused');
    }

    $nameItems.on('hover', pauseScroll);
    $nameItems.on('mouseleave', resumeScroll);

    // Event handlers for mouse enter and leave
    $nameItems.on('mouseenter', pauseScroll);
    $nameItems.on('mouseleave', resumeScroll);

    // For touch devices, manage the scroll state based on touch events
    $('#name-wall').on('touchstart', pauseScroll);
    $('#name-wall').on('touchend', resumeScroll);
});
