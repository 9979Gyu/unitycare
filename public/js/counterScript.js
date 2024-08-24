// Select all count elements
let counters = document.querySelectorAll('.count');

// Update the counts
function updateCounts() {
    counters.forEach(counter => {
        let target = Number(counter.getAttribute('data-target'));
        let count = Number(counter.innerText);

        // If count is already reach target, no need to update
        if (count >= target) return;

        let interval = setInterval(() => {
            count++;
            counter.innerText = count;

            if (count >= target) {
                counter.innerText = target; 
                clearInterval(interval);
            }
        }, 50);
    });
}

// Call updateCounts once to start the counting
updateCounts();