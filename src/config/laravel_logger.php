<?php
    /*
     * TODO
     * Figure out what kind of configs are going to be added
     */
    return [
        'log_connection' => env('YOUR_DATABASE_CONNECTION'), 

        /* Interchangable options available */

        /* Name of table being created */
        //'table_name' => 'logged_events',

        /* Default User model */
        //'user_model' => 'App\User',

        /* Base Model namespace; LaravelLogger will try to auto find models in namespace given */
        //'base_model_namespace' => 'App',

        /* Route pathing */
        //'route_prefix' => 'events',

        /* Default events*/
        //'default_events' => ['created', 'updated', 'deleted', 'retrieved'],

        /* Models that are going to have Events Logged */
        //There are two ways to format loggable_models, you may pass specific options on a individual model.
        //You can also pass the string of the model you want to log. 
        //To be more specific on which events you want to track (if you only pass a string), then you will have to setup through the model 
        //EXAMPLE:
        /*
        'loggable_models' => [
            [
                'model' => 'App\Post',
                'events' => ['created', 'deleted', 'updated', 'retrieved'],
                'attributes' => ['title', 'publisher', 'udpated_at'],
                'log_data' => false,
                'log_user' => false,
            ],
            'App\Comment', 
            'App\User',
            [
                'model' => 'App\UserVote',
                'events' => 'created'
                'attributes' => 'score'
                'log_user' => false,
            ],
            [
                'model' => 'App\Team',
                'attributes' => ['name', 'favorite_animal']
            ],
            [
                'model' => 'App\Role',
                'attributes' => false,
            ],
        ],
         */
    ];
