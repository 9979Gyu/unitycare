function todayDate(){
    const today = new Date();

    const year = today.getFullYear();

    const month = String(today.getMonth() + 1).padStart(2, '0');

    const day = String(today.getDate()).padStart(2, '0');

    const currentDate = `${year}-${month}-${day}`;

    return currentDate;
}

function parseDate(date){
             
    // Format dates
    var newDate = new Date(date);

    // Define the day names array
    var days = ['Ahad', 'Isnin', 'Selasa', 'Rabu', 'Khamis', 'Jumaat', 'Sabtu'];

    // Get the day name using the day number of the week
    var dayName = days[newDate.getDay()];

    // Format the date as "Day, dd-mm-yyyy"
    var formattedDate = dayName + ', ' + ('0' + newDate.getDate()).slice(-2) + '-' + ('0' + (newDate.getMonth() + 1)).slice(-2) + '-' + newDate.getFullYear();

    return formattedDate;

}

// Function to validate start and end dates and times
function validateDateTime(startDate, endDate, startTime, endTime) {
    if(endTime != ''){
        if (startDate > endDate) {
            return false;
        } 
        else if (startDate == endDate && startTime >= endTime) {
            return false;
        }
    }
    return true;
}

function numberWithCommas(num){
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}