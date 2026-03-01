<?php

return [
    'dashboard' => [
        'filters' => [
            'heading' => 'ფილტრი',
            'period' => 'პერიოდი',
            'options' => [
                'all' => 'ALL',
                'yesterday' => 'გუშინ',
                'past_1_month' => '1 თვის წინ',
                'past_3_months' => '3 თვის წინ',
                'past_6_months' => '6 თვის წინ',
                'past_12_months' => '1 წლის წინ',
            ],
        ],
    ],
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
            'user_id' => 'ავტორი',
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
            'delete_only' => [
                'label' => 'მხოლოდ თემის წაშლა',
                'heading' => 'მხოლოდ თემის წაშლა',
                'headingBulk' => 'მხოლოდ თემების წაშლა',
                'description' => 'წაიშლება მხოლოდ თემა. დაკავშირებული დისკუსიის მონაცემები დარჩება.',
            ],
            'delete_with_thread' => [
                'label' => 'თემა + დისკუსიის წაშლა',
                'heading' => 'თემის და დისკუსიის სრული წაშლა',
                'headingBulk' => 'თემების და დისკუსიების სრული წაშლა',
                'description' => 'წაიშლება თემა, ჩატი, შეტყობინებები, დანართები, ლაიქები და მიწოდების ჩანაწერები.',
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
    'conversations' => [
        'singular' => 'საუბარი',
        'plural' => 'საუბრები',
        'fields' => [
            'id' => 'ID',
            'kind' => 'ტიპი',
            'topic_id' => 'თემა',
            'direct_user1_id' => 'მომხმარებელი #1',
            'direct_user2_id' => 'მომხმარებელი #2',
            'private_users' => 'პირადი მონაწილეები',
            'participants_count' => 'მონაწილეების რაოდენობა',
            'participants' => 'მონაწილეები',
            'messages_count' => 'შეტყობინებების რაოდენობა',
            'last_message_at' => 'ბოლო შეტყობინება',
            'created_at' => 'შეიქმნა',
            'updated_at' => 'ბოლოს განახლდა',
        ],
        'kinds' => [
            'topic' => 'თემური',
            'private' => 'პირადი',
        ],
        'titles' => [
            'list' => 'საუბრები',
            'view' => 'საუბრის ნახვა',
        ],
        'actions' => [
            'delete' => [
                'label' => 'წაშლა',
                'heading' => 'საუბრის წაშლა',
                'headingBulk' => 'საუბრების წაშლა',
                'description' => 'წაიშლება საუბარი, მასში არსებული შეტყობინებები და დანართები.',
            ],
            'participants' => [
                'label' => 'მონაწილეები',
                'heading' => 'მონაწილეების სია (:count)',
                'description' => 'მოძებნეთ საუბრის მონაწილეები სახელის მიხედვით.',
                'search_label' => 'ძიება',
                'search_placeholder' => 'შეიყვანეთ მონაწილის სახელი...',
                'search_aria' => 'მონაწილის ძიება',
                'total' => 'სულ',
                'results' => 'ნაპოვნი',
                'empty' => 'მონაწილეები ვერ მოიძებნა',
                'user_fallback' => 'მომხმარებელი #:id',
                'close' => 'დახურვა',
            ],
            'view' => [
                'heading' => 'საუბრის ნახვა',
                'description' => '',
                'actionLabel' => '',
            ],
        ],
        'filters' => [
            'with_topic' => 'თემასთან დაკავშირებული',
            'without_topic' => 'თემის გარეშე',
        ],
    ],
    'messages' => [
        'singular' => 'შეტყობინება',
        'plural' => 'შეტყობინებები',
        'fields' => [
            'id' => 'ID',
            'conversation_id' => 'საუბარი',
            'conversation_kind' => 'საუბრის ტიპი',
            'sender_id' => 'გამომგზავნი',
            'content' => 'ტექსტი',
            'attachments' => 'დანართები',
            'file' => 'ფაილი',
            'attachments_count' => 'დანართების რაოდენობა',
            'attachment_type' => 'დანართის ტიპი',
            'attachment_images' => 'სურათის დანართები',
            'attachment_links' => 'დანართები',
            'original_name' => 'ფაილის სახელი',
            'mime_type' => 'MIME ტიპი',
            'size_bytes' => 'ზომა (MB)',
            'disk' => 'დისკი',
            'path' => 'მისამართი',
            'likes_count' => 'ლაიქების რაოდენობა',
            'created_at' => 'შეიქმნა',
            'updated_at' => 'ბოლოს განახლდა',
            'deleted_at' => 'წაიშალა',
        ],
        'titles' => [
            'list' => 'შეტყობინებები',
            'create' => 'შეტყობინების შექმნა',
            'edit' => 'შეტყობინების რედაქტირება',
            'view' => 'შეტყობინების ნახვა',
        ],
        'actions' => [
            'delete' => [
                'label' => 'წაშლა',
                'heading' => 'შეტყობინების წაშლა',
                'headingBulk' => 'შეტყობინებების წაშლა',
                'description' => 'წაიშლება შეტყობინება, მისი დანართები, ლაიქები და მიწოდების ჩანაწერები.',
            ],
            'delete_attachment' => [
                'heading' => 'დანართის წაშლა',
                'description' => 'დარწმუნებული ხართ, რომ გსურთ დანართის წაშლა? ფაილი შეუქცევადად წაიშლება.',
                'submit' => 'წაშლა',
            ],
        ],
        'filters' => [
            'all' => 'ყველა',
            'deleted_only' => 'მხოლოდ წაშლილი',
            'not_deleted_only' => 'მხოლოდ წაუშლელი',
            'with_sender' => 'გამომგზავნით',
            'without_sender' => 'გამომგზავნის გარეშე',
        ],
        'types' => [
            'image' => 'სურათი',
            'document' => 'დოკუმენტი',
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
    'banners' => [
        'singular' => 'ბანერი',
        'plural' => 'ბანერები',
        'fields' => [
            'key' => 'გასაღები',
            'title' => 'სათაური',
            'subtitle' => 'ქვესათაური',
            'image' => 'სურათი',
            'position' => 'ფონის პოზიცია',
            'overlay_class' => 'გადაფარვის კლასი',
            'container_class' => 'კონტეინერის კლასი',
            'visibility' => 'ხილვადობა',
            'created_at' => 'შეიქმნა',
            'updated_at' => 'ბოლოს განახლდა',
        ],
        'titles' => [
            'edit' => 'ბანერის რედაქტირება',
        ],
        'actions' => [
            'delete' => [
                'heading' => 'ბანერის წაშლა',
                'headingBulk' => 'ბანერების წაშლა',
                'description' => '',
                'actionLabel' => ''
            ],
            'view' => [
                'heading' => 'ბანერის ნახვა',
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
        'stats' => [
            'total' => 'სულ მომხმარებლები',
            'new_current_month' => 'ახალი მომხმარებლები',
            'current_month_range' => 'ამჟამინდელი თვის ჭრილში',
        ],
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
            'clear_email_verified_at' => 'ელ.ფოსტის დამოწმების თარიღის გასუფთავება (ამ ქმედებით მომხმარებელი არაფერიფიცირებული გახდება თუ პარამეტრებში ელ.ფოსტის ვალიდაცია ჩართულია)',
            'clear_phone_verified_at' => 'ტელეფონის დამოწმების თარიღის გასუფთავება (ამ ქმედებით მომხმარებელი არაფერიფიცირებული გახდება თუ პარამეტრებში ტელეფონის ვალიდაცია ჩართულია)',
            'view' => [
                'heading' => 'მომხმარებლის ნახვა',
                'description' => '',
                'actionLabel' => '',
            ],
        ],
    ],
];
