<?php

return [
    'examples' => [
        [
            'label' => 'ექსპერტი',
            'desc' => 'მომხმარებელი, რომელიც აღინიშნა როგორც ექსპერტი. ბეჯი ჩანს ავატარზე და გულისხმობს მაღალი დონის ცოდნას თემატიკაზე.',
            'user' => (object) [
                'name' => 'Expert User',


                'is_expert' => true,
                'is_top_commentator' => false,
            ],
        ],
        [
            'label' => 'ტოპ კომენტატორი',
            'desc' => 'აქტიური და ხარისხიანი კომენტარების ავტორი. ბეჯი ჩანს ავატარზე და უსვამს ხაზს ჩართულობას.',
            'user' => (object) [
                'name' => 'Top Commentator',
                'is_expert' => false,
                'is_top_commentator' => true,
            ],
        ],
    ],
];
