<?php

function getBadge($theme){

    $themeLower = strtolower($theme);

    $badgeClass = match($themeLower){
        "vegan" => "badge-vegan",
        "mariage" => "badge-mariage",
        "noel" => "badge-noel",
        default => "badge-default"
    };

    $icon = match($themeLower){
        "vegan" => "🌱",
        "mariage" => "💍",
        "noel" => "🎄",
        default => "🍽️"
    };

    return [
        "class" => $badgeClass,
        "icon" => $icon
    ];
}