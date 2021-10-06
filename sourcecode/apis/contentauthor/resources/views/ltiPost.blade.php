<html>
<body>
<form method="POST" action="{{$url}}">
    @foreach ($params as $name => $value)
        <input type="hidden" name="{{$name}}" value="{{$value}}">
    @endforeach
        <input type="submit" id="submitbutton" value="Continue">
</form>
<script>
    (function () {
        var submitbutton = document.getElementById("submitbutton");
        var form = submitbutton.form;
        var textElement = document.createTextNode("Please wait...");
        submitbutton.parentNode.appendChild(textElement);
        submitbutton.style.display = 'none';
        form.submit();
    })();
</script>
</body>
</html>
