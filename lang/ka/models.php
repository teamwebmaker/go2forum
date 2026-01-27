<?php

return [
    'settings' => [
        'singular' => 'პარამეტრი',
        'plural' => 'პარამეტრები',
        'fields' => [
            'is_phone_verification_enabled' => 'ტელეფონის ვალიდაცია',
            'is_email_verification_enabled' => 'ელ.ფოსტის ვალიდაცია',
            'created_at' => 'შეიქმნა',
            'updated_at' => 'ბოლოს განახლდა',
        ],
        'titles' => [
            'edit' => 'პარამეტრების რედაქტირება',
        ],
    ],
    'categories' => [
        'singular' => 'კატეგორია',
        'plural' => 'კატეგორიები',
        'fields' => [
            'name' => 'სახელი',
            'ad_id' => 'რეკლამა',
            'topics_count' => 'თემების რაოდენობა',
            'order' => 'რიგითობა',
            'visibility' => 'ხილვადობა',
            'created_at' => 'შეიქმნა',
            'updated_at' => 'ბოლოს განახლდა',
        ],
        'titles' => [
            'edit' => 'კატეგორიის რედაქტირება',
        ],
        'actions' => [
            'delete' => [
                'heading' => 'კატეგორიის წაშლა',
                'headingBulk' => 'კატეგორიების წაშლა',
                'description' => '',
                'actionLabel' => ''
            ]
        ]
    ],
    'ads' => [
        'singular' => 'რეკლამა',
        'plural' => 'რეკლამები',
        'fields' => [
            'name' => 'სახელი',
            'image' => 'სურათი',
            'link' => 'ბმული',
            'visibility' => 'ხილვადობა',
            'created_at' => 'შეიქმნა',
            'updated_at' => 'ბოლოს განახლდა',
        ],
        'titles' => [
            'edit' => 'რეკლამის რედაქტირება',
        ],
        'actions' => [
            'delete' => [
                'heading' => 'რეკლამის წაშლა',
                'headingBulk' => 'რეკლამების წაშლა',
                'description' => '',
                'actionLabel' => ''
            ],
            'view' => [
                'heading' => 'რეკლამის ნახვა',
                'description' => '',
                'actionLabel' => '',
            ],
        ]
    ],
    'public_documents' => [
        'singular' => 'დოკუმენტი',
        'plural' => 'დოკუმენტები',
        'fields' => [
            'name' => 'სახელი',
            'document' => 'დოკუმენტი',
            'link' => 'ბმული',
            'order' => 'რიგითობა',
            'visibility' => 'ხილვადობა',
            'created_at' => 'შეიქმნა',
            'updated_at' => 'ბოლოს განახლდა',
        ],
        'titles' => [
            'edit' => 'დოკუმენტის რედაქტირება',
        ],
        'actions' => [
            'delete' => [
                'heading' => 'დოკუმენტის წაშლა',
                'headingBulk' => 'დოკუმენტების წაშლა',
                'description' => '',
                'actionLabel' => ''
            ]
        ]
    ],
];
