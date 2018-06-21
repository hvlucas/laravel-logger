<?php
    return [
        /* Database connection which will store events */
        //'log_connection' => env('YOUR_DATABASE_CONNECTION'), 

        /* Interchangable options available */

        /* Name of table being created */
        //'table_name' => 'logged_events',

        /* Default User model */
        //'user_model' => 'App\User',
        
        /* User column to be displayed in the front-end */
        //'user_column' => 'name',

        /* Path for Laravel Logger to auto-discover models */
        //'discovery_path' => 'app/',
        
        /* Base Model namespace; Laravel Logger will try to auto find models in namespace given */
        //'discover_namespace' => 'App',

        /* Route pathing */
        //'route_prefix' => 'events',

        /* Models that are going to have Events Logged */
        //There are two ways to format loggable_models, you may pass specific options on a individual model.
        //You can also pass the string of the model you want to log. 
        //To be more specific on which events you want to track (if you only pass a string), then you will have to setup through the model 
        //EXAMPLE:
        /*
        'loggable_models' => [
            [
                'model' => 'App\Post',
                'trackable_attributes' => ['title', 'publisher', 'udpated_at'],
                'sync_attributes' => ['title'],
                'tracks_data' => true,
                'tracks_user' => true,
                'only_when_authenticated' => true,
            ],
            'App\Comment', 
            'App\User',
            [
                'model' => 'App\UserVote',
                'attributes' => 'score'
                'log_user' => false,
            ],
            [
                'model' => 'App\Team',
                'trackable_attributes' => ['name', 'favorite_animal']
            ],
            [
                'model' => 'App\Role',
                'trackable_attributes' => false,
            ],
        ],
         */
    ];
