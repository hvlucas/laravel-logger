<?php
    /*
     * TODO
     * Figure out what kind of configs are going to be added
     */
    return [
        'log_connection' => env('YOUR_DATABASE_CONNECTION'), 

        /* Interchangable options available */

        /* Displays attributes being changed */
        //'with_data' => true,
        
        /* Name of table being created */
        //'table_name' => 'logged_events',

        /* Default User model */
        //'user_model' => 'App\User',

        /* Base Model namespace; LaravelLogger will try to auto find models in namespace given */
        //'base_model_namespace' => 'App',

        /* Route pathing */
        //'route_prefix' => 'events',


        /* Models that are going to have Events Logged */
        //'loggable_models' => []
    ];
