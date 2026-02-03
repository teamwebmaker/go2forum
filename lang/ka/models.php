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
    'topics' => [
        'singular' => 'თემა',
        'plural' => 'თემები',
        'fields' => [
            'user_id' => 'მომხმარებელი',
            'category_id' => 'კატეგორია',
            'title' => 'სათაური',
            'status' => 'სტატუსი',
            'slug' => 'სლაგი',
            'messages_count' => 'შეტყობინებების რაოდენობა',
            'pinned' => 'აპინული',
            'visibility' => 'ხილვადობა',
            'created_at' => 'შეიქმნა',
            'updated_at' => 'ბოლოს განახლდა',
        ],
        'statuses' => [
            'active' => 'აქტიური',
            'closed' => 'დახურული',
            'disabled' => 'გათიშული',
        ],
        'titles' => [
            'create' => 'თემის შექმნა',
            'edit' => 'თემის რედაქტირება',
        ],
        'actions' => [
            'delete' => [
                'heading' => 'თემის წაშლა',
                'headingBulk' => 'თემების წაშლა',
                'description' => '',
                'actionLabel' => ''
            ],
            'view' => [
                'heading' => 'თემის ნახვა',
                'description' => '',
                'actionLabel' => '',
            ],
        ],
        'filters' => [
            'with_category' => 'კატეგორიით',
            'without_category' => 'კატეგორიის გარეშე',
        ],
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
    'users' => [
        'singular' => 'მომხმარებელი',
        'plural' => 'მომხმარებლები',
        'fields' => [
            'name' => 'სახელი',
            'surname' => 'გვარი',
            'email' => 'ელ.ფოსტა',
            'phone' => 'ტელეფონი',
            'role' => 'როლი',
            'image' => 'სურათი',
            'is_expert' => 'ექსპერტი',
            'is_top_commentator' => 'ტოპ კომენტატორი',
            'is_blocked' => 'დაბლოკილი',
            'email_verified_at' => 'ელ.ფოსტა დამოწმდა',
            'phone_verified_at' => 'ტელეფონი დამოწმდა',
            'password' => 'პაროლი',
            'created_at' => 'შეიქმნა',
            'updated_at' => 'ბოლოს განახლდა',
        ],
        'titles' => [
            'create' => 'მომხმარებლის შექმნა',
            'edit' => 'მომხმარებლის რედაქტირება',
        ],
        'actions' => [
            'delete' => [
                'heading' => 'მომხმარებლის წაშლა',
                'headingBulk' => 'მომხმარებლების წაშლა',
                'description' => '',
                'actionLabel' => ''
            ],
            'view' => [
                'heading' => 'მომხმარებლის ნახვა',
                'description' => '',
                'actionLabel' => '',
            ],
        ],
    ],
];
