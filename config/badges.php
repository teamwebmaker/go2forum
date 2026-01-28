<?php

return [
    'examples' => [
        [
            'label' => 'ექსპერტი',
            'desc' => 'მომხმარებელი, რომელიც აღინიშნა როგორც ექსპერტი. ბეჯი ჩანს ავატარზე და გულისხმობს მაღალი დონის ცოდნას თემატიკაზე.',
            'user' => (object) [
                'name' => 'Expert User',
                'initials' => '',
                'avatar_url' => null,
                'is_expert' => true,
                'is_top_commentator' => false,
            ],
        ],
        [
            'label' => 'ტოპ კომენტატორი',
            'desc' => 'აქტიური და ხარისხიანი კომენტარების ავტორი. ბეჯი ჩანს ავატარზე და უსვამს ხაზს ჩართულობას.',
            'user' => (object) [
                'name' => 'Top Commentator',
                'initials' => '',
                'avatar_url' => null,
                'is_expert' => false,
                'is_top_commentator' => true,
            ],
        ],
        [
            'label' => 'ორივე სტატუსი',
            'desc' => 'მომხმარებელი, რომელსაც ერთდროულად აქვს ორივე სტატუსი. ავატარზე ნაჩვენებია ორი ბეჯი.',
            'user' => (object) [
                'name' => 'Elite User',
                'initials' => '',
                'avatar_url' => null,
                'is_expert' => true,
                'is_top_commentator' => true,
            ],
        ],
    ],
];
