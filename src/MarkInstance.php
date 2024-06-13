<?php

namespace marksync\provider;

#[\Attribute(\Attribute::TARGET_CLASS)]
class MarkInstance {

    function __construct(
        public string | null $title = null,
    ) {
        
    }

}