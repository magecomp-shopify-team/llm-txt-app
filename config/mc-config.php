<?php

return [
    "urlRedirects" => [
        [
            "path" => "/llms.txt",
            "target" => "/a/llms",
        ]
    ],
    'plan_config' => [
        1 => [
            "name" => "Basic",
            "products" => 50,
            "collections" => 10,
            "pages" => 0,
            "blogs" => 0,
        ],
        2 => [
            "name" => "Basic - Annual",
            "products" => 50,
            "collections" => 10,
            "pages" => 0,
            "blogs" => 0,
        ],
        3 => [
            "name" => "Advance",
            "products" => 200,
            "collections" => 50,
            "pages" => 20,
            "blogs" => 30,
        ],
        4 => [
            "name" => "Advance - Annual",
            "products" => 200,
            "collections" => 50,
            "pages" => 20,
            "blogs" => 30,
        ],
        5 => [
            "name" => "Pro",
            "products" => 'ALL',
            "collections" => 'ALL',
            "pages" => 'ALL',
            "blogs" => 'ALL',
        ],
        6 => [
            "name" => "Pro - Annual",
            "products" => 'ALL',
            "collections" => 'ALL',
            "pages" => 'ALL',
            "blogs" => 'ALL',
        ]
    ]
];
