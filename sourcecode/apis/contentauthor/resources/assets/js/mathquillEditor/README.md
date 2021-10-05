## Mathquill based WUSIWYG editor

> MathQuill is an awesome formula editor. We have put some effort and made WUSIWUG editor on top of that.

## What is this?
> This is a javascript library that provides you a simple math editor that you can easily integrate to your webpage.

## Dependencies
> - [jQuery](https://jquery.com/download/)
- [MathQuill](https://github.com/mathquill/mathquill)

## Getting Started
Download this package and extract files to your project lib folder.

Include required JS and CSS files to your webpage.
```
<html>
<head>
<link href="./path/to/mathquill.css" rel="stylesheet">
<link href="./path/to/matheditor.css" rel="stylesheet">

<script src="./path/to/jquery.js"></script>
<script src="./path/to/mathquill.min.js"></script>
<script src="./path/to/matheditor.js"></script>
</head>
<body>
.
.
.
.
</body>
</html>
```

Create MathEditor Instance and set all the options you required.
```javascript

var mathEditor = new MathEditor('some_id');

mathEditor.addButtons(["fraction","square_root","cube_root","root",'superscript','subscript']);
// If you dont write this line editor will display default buttons. 

mathEditor.removeButtons(["fraction","square_root"])
// If you want to remove some buttons from default list.

mathEditor.styleMe({
    width: '500',
    height: '80'
});
// List of other options are mentioned bellow.

mathEditor.setTemplate('floating-toolbar');
// It will make button toolbar floating.

mathEditor.getLatex();
// It will return letex for input formula.

mathEditor.setLatex('\\frac{1}{2}');
// It will set letex in input area.
// It accepts any latex string.
```

## Options
### styleMe()
Attribute | Type | Default | Description
--------- | ---- | ------- | -----------
width|`string`|`500`|It will define minimum width for your editor
height|`string`|`40`|It will define minimum height for your editor
textarea_background|`string`|`#FFFFFF`|Background color for your editor textarea<br>eg. 'white', '#FFFFFF', 'rgba(255,255,255,0.5)'
textarea_foreground|`string`|`#000000`|Text color for your editor textarea<br>eg. 'white', '#FFFFFF', 'rgba(255,255,255,0.5)'
textarea_border|`string`|`#000000`|Border color for your editor textarea<br>eg. 'white', '#FFFFFF', 'rgba(255,255,255,0.5)'
toolbar_background|`string`|`#FFFFFF`|Background color for your editor toolbar<br>eg. 'white', '#FFFFFF', 'rgba(255,255,255,0.5)'
toolbar_foreground|`string`|`#000000`|Text color for your editor toolbar<br>eg. 'white', '#FFFFFF', 'rgba(255,255,255,0.5)'
toolbar_border|`string`|`#000000`|Border color for your editor toolbar<br>eg. 'white', '#FFFFFF', 'rgba(255,255,255,0.5)'
button_background|`string`|`#FFFFFF`|Background color for your editor toolbar button<br>eg. 'white', '#FFFFFF', 'rgba(255,255,255,0.5)'
button_border|`string`|`#000000`|Border color for your editor toolbar button<br>eg. 'white', '#FFFFFF', 'rgba(255,255,255,0.5)'

## TinyMCE Support
- You can integrate our MathEditor to tinyMCE with easy implementation.
- [Click here](https://github.com/SinghSatyam/math_editor) for documentation.

## Help us Improve
#### [Donate us](https://www.paypal.me/KBhutwala)

