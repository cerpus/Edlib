@extends('layouts.resource')

@push('js')
    <script src="/js/ckeditor/ckeditor.js"></script>
    <script src="{{mix('react-questionset.js')}}"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.5/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>
    <script src="{{mix('js/question-editor.js')}}"></script>
@endpush

@push('css')
    <link rel="stylesheet" href="{{ mix('react-questionset.css') }}">
@endpush

