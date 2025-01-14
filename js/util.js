function generate_token(length) {

    const characters ='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz@-+^$*!';
    let result = '';
    const charactersLength = characters.length;
    for ( let i = 0; i < length; i++ ) {
        result += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    return result;
}

function getMonth2Digits(date) {
    // 👇️ Add 1, because getMonth is 0-11
    const month = date.getMonth() + 1;
    if (month < 10) {
        return '0' + month;
    }
    return month;
}

function getDay2Digits(date) {
    const day = date.getDate();
    if (day < 10) {
        return '0' + day;
    }
    return day;
}

function PrintPage() {
    window.print();
}


