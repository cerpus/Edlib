<?php

declare(strict_types=1);

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
set_exception_handler(fn (Throwable $e) => error((string) $e));
set_error_handler(function (int $code, string $message, string $file, int $line): true {
    if (!($code & ~error_reporting())) {
        error("Error: $message on $file:$line");
    }
    return true;
});
libxml_use_internal_errors(true);

match (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)) {
    '/' => health(),
    '/translateNHtml' => translate(),
    default => error('Not found', 404),
};

// routes

function health(): void
{
    echo 'ok';
}

function translate(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        error('A POST request was expected', 405);
    }

    if (!isset($_FILES['file']['tmp_name'])) {
        error('A file with the key "file" was expected', 400);
    }

    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        error(sprintf('Something happened with the file upload (error %d)', $_FILES['file']['error']));
    }

    $dom = new DOMDocument();
    $dom->loadHTMLFile($_FILES['file']['tmp_name']);

    if ($dom->documentElement !== null) {
        translate_text_nodes($dom->documentElement);
    }

    echo $dom->saveHTML();
}

function error(string $message, int $code = 500): never
{
    if ($code >= 500) {
        log_message("[$code] $message");
    }

    if (!headers_sent()) {
        header('HTTP/1.1 ' . $code);
        header('Content-Type: text/plain; charset=UTF-8');
    }

    die($message . "\n");
}

// helpers

function log_message(string $message): void
{
    file_put_contents('php://stderr', $message . PHP_EOL, FILE_APPEND);
}

function translate_text(string $original): string
{
    return str_replace([
        'a', 'e', 'i', 'o', 'u', 'æ', 'ø', 'å',
        'A', 'E', 'I', 'O', 'U', 'Æ', 'Ø', 'Å',
    ], [
        'ã', 'ë', 'ï', 'õ', 'ü', 'ä', 'ö', 'á',
        'Ã', 'Ë', 'Ï', 'Õ', 'Ü', 'Ä', 'Ö', 'Á',
    ], $original);
}

function translate_text_nodes(DOMElement $element) {
    foreach ($element->childNodes as $node) {
        if ($node instanceof DOMText) {
            $translatedNode = new DOMText(translate_text($node->textContent));

            $element->replaceChild($translatedNode, $node);
        } elseif ($node instanceof DOMElement) {
            translate_text_nodes($node);
        }
    }
}
