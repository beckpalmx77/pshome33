function dateAgo(date) {
    let startDate = new Date(date);
    let diffDate = new Date(new Date() - startDate);
    return ((diffDate.toISOString().slice(0, 4) - 1970) + "Y " +
        diffDate.getMonth() + "M " + (diffDate.getDate()-1) + "D");
}

function CalDay(date_1,date_2) {

    let date1 = new Date(date_1);
    let date2 = new Date(date_2);

    // To calculate the time difference of two dates
    let Difference_In_Time = date2.getTime() - date1.getTime();

    // To calculate the no. of days between two dates
    let Difference_In_Days = Difference_In_Time / (1000 * 3600 * 24);

    return Difference_In_Days;

}

function getAge(dateString) {
    let now = new Date();
    let today = new Date(now.getYear(),now.getMonth(),now.getDate());

    let yearNow = now.getYear();
    let monthNow = now.getMonth();
    let dateNow = now.getDate();

    let dob = new Date(dateString.substring(6,10),
        dateString.substring(0,2)-1,
        dateString.substring(3,5)
    );

    let yearDob = dob.getYear();
    let monthDob = dob.getMonth();
    let dateDob = dob.getDate();
    let age = {};
    let ageString = "";
    let yearString = "";
    let monthString = "";
    let dayString = "";
    let yearAge = "";
    let monthAge = "";
    let dateAge ="";


    yearAge = yearNow - yearDob;

    if (monthNow >= monthDob)
        monthAge = monthNow - monthDob;
    else {
        yearAge--;
        monthAge = 12 + monthNow -monthDob;
    }

    if (dateNow >= dateDob)
        dateAge = dateNow - dateDob;
    else {
        monthAge--;
        dateAge = 31 + dateNow - dateDob;

        if (monthAge < 0) {
            monthAge = 11;
            yearAge--;
        }
    }

    age = {
        years: yearAge,
        months: monthAge,
        days: dateAge
    };

    if ( age.years > 1 ) yearString = " ปี";
    else yearString = " ปี";
    if ( age.months> 1 ) monthString = " เดือน";
    else monthString = " เดือน";
    if ( age.days > 1 ) dayString = " วัน";
    else dayString = " วัน";


    if ( (age.years > 0) && (age.months > 0) && (age.days > 0) )
        ageString = age.years + yearString + " " + age.months + monthString + " " + age.days + dayString ;
    else if ( (age.years == 0) && (age.months == 0) && (age.days > 0) )
        ageString = age.days + dayString ;
    else if ( (age.years > 0) && (age.months == 0) && (age.days == 0) )
        ageString = age.years + yearString + " ";
    else if ( (age.years > 0) && (age.months > 0) && (age.days == 0) )
        ageString = age.years + yearString + " " + age.months + monthString ;
    else if ( (age.years == 0) && (age.months > 0) && (age.days > 0) )
        ageString = age.months + monthString + " " + age.days + dayString ;
    else if ( (age.years > 0) && (age.months == 0) && (age.days > 0) )
        ageString = age.years + yearString + " " + age.days + dayString ;
    else if ( (age.years == 0) && (age.months > 0) && (age.days == 0) )
        ageString = age.months + monthString ;
    else ageString = "ไม่สามารถคำนวนได้ ในขณะนี้";

    return ageString;


}