<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Hello, modularity!</title>
</head>
<body>
<div class="container">

    <div>
        <h1>Hello, modularity!</h1>
        <button id="popoverButton" type="button" class="btn btn-primary btn-lg" data-bs-toggle="popover"
                title="ESM in Browser"
                data-bs-content="Bang!">Custom popover
        </button>
    </div>

    <div class="card" style="width: 18rem;">
        <img class="card-img-top" src="..." alt="Card image cap">
        <div class="card-body">
            <h5 class="card-title">Card title</h5>
            <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's
                content.</p>
            <a href="#" class="btn btn-primary">Go somewhere</a>
        </div>
    </div>

</div>


<script async src="https://cdn.jsdelivr.net/npm/es-module-shims@1/dist/es-module-shims.min.js"
        crossorigin="anonymous"></script>
<script type="importmap">
    {
      "imports": {
        "@popperjs/core": "https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/esm/popper.min.js",
        "bootstrap": "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.esm.min.js"
      }
    }


</script>
<script type="module">
    import * as bootstrap from 'bootstrap'

    new bootstrap.Popover(document.getElementById('popoverButton'))
</script>
</body>
</html>
