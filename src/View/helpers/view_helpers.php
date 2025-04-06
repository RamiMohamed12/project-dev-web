<?php

// Ensure this function isn't declared multiple times if the file is included elsewhere
if (!function_exists('displayStars')) {
    /**
     * Generates HTML for displaying star ratings based on a score.
     *
     * @param float $rating The rating score (0-5).
     * @param string $filledClass CSS class for a filled star (Font Awesome).
     * @param string $halfClass CSS class for a half star (Font Awesome).
     * @param string $emptyClass CSS class for an empty star (Font Awesome).
     * @return string HTML string representing the stars.
     */
    function displayStars(float $rating, string $filledClass = 'fas fa-star star-filled', string $halfClass = 'fas fa-star-half-alt star-half', string $emptyClass = 'far fa-star star-empty'): string
    {
        $rating = max(0, min(5, $rating)); // Ensure rating is between 0 and 5
        $output = '';
        $fullStars = floor($rating);
        $halfStar = ($rating - $fullStars) >= 0.5;
        $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);

        // Add full stars
        for ($i = 0; $i < $fullStars; $i++) {
            $output .= '<i class="' . htmlspecialchars($filledClass) . '"></i>';
        }

        // Add half star if applicable
        if ($halfStar) {
            $output .= '<i class="' . htmlspecialchars($halfClass) . '"></i>';
        }

        // Add empty stars
        for ($i = 0; $i < $emptyStars; $i++) {
            $output .= '<i class="' . htmlspecialchars($emptyClass) . '"></i>';
        }

        // Add some CSS classes to the star icons for easier styling (optional but recommended)
        // Example CSS you might add:
        /*
        .star-rating i { color: #f8b400; margin-right: 2px; }
        .star-rating .star-empty { color: #ccc; }
        */

        return $output;
    }
}

// You can add other view-related helper functions here...

?>
