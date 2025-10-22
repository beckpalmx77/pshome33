<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>javascript difference between two dates in years months days</title>
</head>
<body>
  <script type = "text/javascript">
    function diff_year_month_day(dt1, dt2)
    {

        let time =(dt2.getTime() - dt1.getTime()) / 1000;
        let year  = Math.abs(Math.round((time/(60 * 60 * 24))/365.25));
        let month = Math.abs(Math.round(time/(60 * 60 * 24 * 7 * 4)));
        let days = Math.abs(Math.round(time/(3600 * 24)));
        return "Year :- " + year + " Month :- " + month + " Days :-" + days;

    }

    dt1 = new Date("2019-11-27");
    dt2 = new Date("2018-06-28");

    let diff_year_month_day = diff_year_month_day(dt1, dt2)
    document.write( "javascript difference between two dates in years months days :- " + diff_year_month_day );

</script>

</body>
</html>