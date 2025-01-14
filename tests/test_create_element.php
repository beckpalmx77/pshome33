<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<div id="myHTMLWrapper"></div>

<div id="myHTMLWrapper1"></div>

<script>
    let wrapper = document.getElementById("myHTMLWrapper");
    let myHTML = '';
    for (let i = 0; i < 10; i++) {
        myHTML += '<span class="test">Testing out my script! loop #' + (i + 1) + '</span><br/><br/>';
    }
    wrapper.innerHTML = myHTML

</script>

<script>
    let wrapper1 = document.getElementById("myHTMLWrapper1");
    let myHTML1 = '';
    for (let i = 0; i < 10; i++) {
        myHTML1 += '<span class="test">Testing out my script! loop #' + (i + 1) + '</span><br/><br/>';
    }
    wrapper1.innerHTML = myHTML1

</script>

</body>
</html>