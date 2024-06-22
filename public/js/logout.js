// control user timeout

let logoutTimer;

function resetLogoutTimer() {
    clearTimeout(logoutTimer);
    logoutTimer = setTimeout(logoutUser, 120 * 60 * 1000); // 2 hours
}

function logoutUser() {
    $.ajax({
        type: 'POST',
        url: "/logout",
        success: function(response) {
            window.location.href = '/';
        },
        error: function(xhr, status, error) {
            console.error('Logout failed:', error);
        }
    });
}

// csrf token for ajax
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Add event listeners to detect user activity
document.addEventListener('mousemove', resetLogoutTimer);
document.addEventListener('keypress', resetLogoutTimer);
document.addEventListener('scroll', resetLogoutTimer);
document.addEventListener('touchstart', resetLogoutTimer);

// Initialize timer on page load
resetLogoutTimer();


