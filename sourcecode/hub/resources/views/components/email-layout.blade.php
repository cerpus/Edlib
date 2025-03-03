{{--
  Before changing this, note that some commonly used email clients are stuck in
  the stone ages (as of 2025), and don't support the HTML5 doctype or the
  <style> element.
--}}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <title>{{ $title }}</title>
    </head>

    <body style="font-family: helvetica, arial, sans-serif; line-height: 1.5; max-width: 600px">
        {{ $slot }}
    </body>
</html>
