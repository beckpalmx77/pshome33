
let originalURL = "https://www.example.com/?q=JavaScript และ URL Encoding";

let encodedURL = encodeURL(originalURL);
console.log("Encoded URL:", encodedURL);

let decodedURL = decodeURL(encodedURL);
console.log("Decoded URL:", decodedURL);


function encodeURL(url) {
    return encodeURIComponent(url);
}

function decodeURL(url) {
    return encodeURIComponent(url);
}
