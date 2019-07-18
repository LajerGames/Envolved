<?php

return [
    'users_who_can_register' => [1],
    'character_roles' => [
        'protagonist' => 'Progatonist',
        'major_character' => 'Major Character',
        'minor_character' => 'Minor Character',
        'journalist' => 'Journalist'
    ],
    'character_roles_wo_protagonist' => [
        'major_character' => 'Major Character',
        'minor_character' => 'Minor Character',
        'journalist' => 'Journalist'
    ],
    'variable_types' => [
        'text' => 'Text',
        'number' => 'Number (integer)',
        'float' => 'Float (decimal)'
    ],
    'genders' => [
        'male' => 'Male',
        'female' => 'Female'
    ],
    'story_points' => [
        'change_variable' => ['Change Variable', 'orange'],
        'wait' => ['Wait', 'grey'],
        'condition' => ['Variable Condition', 'purple'],
        'change_master_data' => ['Change Master Data', 'pink'],
        'text_incomming' => ['Text, Incomming', 'lightgreen'],
        'text_outgoing' => ['Text, Outgoing', 'green'],
        'phone_call_incomming_voice' => ['Phone Call, Incomming Voice', 'lightblue'],
        'phone_call_outgoing_voice' => ['Phone Call, Incomming Voice', 'blue'],
        'insert_news_item'  => ['Insert news item', 'yellow']
    ]
];