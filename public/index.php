<?php
require_once '../vendor/autoload.php';


function json($value, ?int $options = null, int $dept = 512): void
{
    if (($value instanceof JsonSerializable) === false && is_array($value) === false) {
        throw new InvalidArgumentException('Invalid type for parameter "value". Must be of type array or object implementing the \JsonSerializable interface.');
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($value, $options, $dept);
    exit(0);
}

\Clrkz\parseJson::main();
